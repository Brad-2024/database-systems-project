<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
requireRole(['coach', 'athlete']);

$userName = $_SESSION['user_name'] ?? 'User';
$userRole = $_SESSION['user_role'] ?? 'Role';
$userID = $_SESSION['user_id'] ?? -1;

$connection = getDatabaseConnection();

$query_num_athlete = "SELECT COUNT(*) AS num_athletes FROM users WHERE role = 'athlete'";
$result_num_athlete = mysqli_query($connection, $query_num_athlete);
$num_athlete = mysqli_fetch_assoc($result_num_athlete);

//Views 

//ActiveInjuries
$query_injuries = "SELECT id, first_name, last_name, type, occurence_date FROM ActiveInjuries";
$result_injuries = mysqli_query($connection, $query_injuries);
$injuries = mysqli_fetch_all($result_injuries, MYSQLI_ASSOC);

//WeeklyTrainingSuccessScore
$query_success = "SELECT athlete_id, first_name, last_name, average_success_score FROM WeeklyTrainingSuccessScore";
$result_success = mysqli_query($connection, $query_success);
$success_scores = mysqli_fetch_all($result_success, MYSQLI_ASSOC);

//WeeklyTrainingDistance
$query_distance = "SELECT athlete_id, first_name, last_name, average_distance FROM WeeklyTrainingDistance";
$result_distance = mysqli_query($connection, $query_distance);
$distances = mysqli_fetch_all($result_distance, MYSQLI_ASSOC);

//BestPerformanceTrendsPerEvent
$query_best = "SELECT athlete_id, first_name, last_name, event, best_time FROM BestPerformanceTrendsPerEvent";
$result_best = mysqli_query($connection, $query_best);
$best_rows = mysqli_fetch_all($result_best, MYSQLI_ASSOC);

//CurrentPerformanceTrendsPerEvent
$query_current = "SELECT athlete_id, first_name, last_name, event, most_recent_time FROM CurrentPerformanceTrendsPerEvent";
$result_current = mysqli_query($connection, $query_current);
$current_rows = mysqli_fetch_all($result_current, MYSQLI_ASSOC);

//TrainingReadinessIndicator
$query_readiness = "SELECT athlete_id, first_name, last_name, avg_workout_success_7_days, active_injuries, avg_daily_calories_30_days, training_readiness_indicator FROM TrainingReadinessIndicator";
$result_readiness = mysqli_query($connection, $query_readiness);
$readiness_rows = mysqli_fetch_all($result_readiness, MYSQLI_ASSOC);

//build a lookup: [athlete_id][event] => best_time
$best_lookup = [];
foreach ($best_rows as $row) {
    $best_lookup[$row['athlete_id']][$row['event']] = (float)$row['best_time'];
}

//build per-event delta data: event => [ {name, delta} ]
$event_deltas = [];
foreach ($current_rows as $row) {
    $aid = $row['athlete_id'];
    $event = $row['event'];
    $current_time = (float)$row['most_recent_time'];
    $best_time = $best_lookup[$aid][$event] ?? null;

    if ($best_time !== null) {
        $delta = round($current_time - $best_time, 2);
        $event_deltas[$event][] = [
            'name' => $row['last_name'] . ', ' . substr($row['first_name'], 0, 1) . '.',
            'delta' => $delta
        ];
    }
}

//summary counts
$total_athletes = $num_athlete['num_athletes'] ?? 0;
$total_injuries = count($injuries);
$avg_success = $total_athletes > 0
    ? round(array_sum(array_column($success_scores, 'average_success_score')) / $total_athletes, 1)
    : 0;
$avg_distance = count($distances) > 0
    ? round(array_sum(array_column($distances, 'average_distance')) / count($distances), 1)
    : 0;
$readiness_counts = [
    'R' => 0,
    'O' => 0,
    'G' => 0
];

foreach ($readiness_rows as $row) {
    $indicator = $row['training_readiness_indicator'];

    if (isset($readiness_counts[$indicator])) {
        $readiness_counts[$indicator]++;
    }
}

//JSON for JS
$event_deltas_json = json_encode($event_deltas);
$success_json = json_encode($success_scores);
$distance_json = json_encode($distances);
$readiness_json = json_encode($readiness_counts);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Dashboard | Athletic Performance Hub</title>
    <link rel="stylesheet" href="css/main.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
</head>
<body>

<?php require_once __DIR__ . '/../includes/nav.php'; ?>

<div class="container">

    <h2>Team Dashboard</h2>
    <p class="page-subtitle">Weekly overview — training, performance, and injury status</p>

    <?php if ($userRole === 'coach'): ?>
    <!-- Summary Cards -->
    <div class="dashboard-grid">
        <div class="summary-card">
            <p class="card-label">Total athletes</p>
            <p class="card-value"><?= $total_athletes ?></p>
            <p class="card-sub">on roster</p>
        </div>
        <div class="summary-card">
            <p class="card-label">Active injuries</p>
            <p class="card-value danger"><?= $total_injuries ?></p>
            <p class="card-sub">require monitoring</p>
        </div>
        <div class="summary-card">
            <p class="card-label">Avg training score</p>
            <p class="card-value"><?= $avg_success ?><span style="font-size:1rem;font-weight:400;color:#aaa;">/10</span></p>
            <p class="card-sub">past 7 days</p>
        </div>
        <div class="summary-card">
            <p class="card-label">Avg daily distance</p>
            <p class="card-value"><?= $avg_distance ?><span style="font-size:1rem;font-weight:400;color:#aaa;"> mi</span></p>
            <p class="card-sub">past 7 days</p>
        </div>
    </div>

    <!-- Best vs Current Times -->
    <div class="chart-panel">
        <h3>Best vs. current times by event</h3>
        <p class="chart-desc">Positive = slower than personal best (regression). Negative = faster (improvement).</p>

        <div class="event-tabs" id="eventTabs"></div>

        <div class="chart-legend">
            <span><span class="leg-dot" style="background:#5dcaa5;"></span>Improving</span>
            <span><span class="leg-dot" style="background:#e24b4a;"></span>Regressing</span>
        </div>

        <div style="position:relative;width:100%;height:260px;">
            <canvas id="timeDeltaChart"></canvas>
        </div>
    </div>

    <!-- Training Score + Distance -->
    <div class="two-col">
        <div class="chart-panel">
            <h3>Weekly training success score</h3>
            <p class="chart-desc">7-day average (0–10 scale)</p>
            <div style="position:relative;width:100%;height:320px;">
                <canvas id="successChart"></canvas>
            </div>
        </div>
        <div class="chart-panel">
            <h3>Weekly training distance</h3>
            <p class="chart-desc">Average daily meters over 7 days</p>
            <div style="position:relative;width:100%;height:320px;">
                <canvas id="distanceChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Active Injuries -->
    <div class="chart-panel">
        <h3>Active injuries</h3>
        <p class="chart-desc">Athletes currently under injury restriction</p>

        <?php if (count($injuries) === 0): ?>
            <p style="color:#888;font-size:0.875rem;">No active injuries — great news!</p>
        <?php else: ?>
            <table class="injury-table">
                <thead>
                    <tr>
                        <th>Athlete</th>
                        <th>Injury type</th>
                        <th>Since</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($injuries as $injury): ?>
                        <tr>
                            <td><?= htmlspecialchars($injury['last_name'] . ', ' . $injury['first_name']) ?></td>
                            <td><?= htmlspecialchars($injury['type']) ?></td>
                            <td><?= htmlspecialchars(date('M j, Y', strtotime($injury['occurence_date']))) ?></td>
                            <td><span class="injury-badge">Active</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Training Readiness Indicator -->
    <div class="chart-panel">
        <h3>Training readiness indicator</h3>
        <p class="chart-desc">Team readiness based on active injuries, 7-day workout success, and 30-day average daily calories</p>

        <div class="chart-legend">
            <span><span class="leg-dot" style="background:#e24b4a;"></span>Red</span>
            <span><span class="leg-dot" style="background:#ef9f27;"></span>Orange</span>
            <span><span class="leg-dot" style="background:#5dcaa5;"></span>Green</span>
        </div>

        <div style="position:relative;width:100%;height:320px;">
            <canvas id="readinessChart"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($userRole === 'athlete'): ?>
    <div>
        <form method="POST" action="add-workout.php">
            <h1>Add Workout:</h1>
            <table>
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Workout Success</th>
                </tr>
                </thead>
                <tbody>
                <td>
                    <input
                            type="date"
                            name="date"
                            required
                    >
                </td>
                <td>
                    <input
                            type="number"
                            max="10"
                            min="0"
                            name="workout_success"
                            required
                    >
                </td>
                </tbody>
            </table>
            <table id="workoutTable">
                <thead>
                <tr>
                    <th>Set #</th>
                    <th>Distance</th>
                    <th>Time</th>
                    <th>Remove</th>
                </tr>
                </thead>

                <tbody>
                <tr>
                    <td>1</td>

                    <td>
                        <input
                                type="number"
                                name="distance[]"
                                placeholder="400"
                                required
                        >
                    </td>

                    <td>
                        <input
                                type="number"
                                name="time[]"
                                placeholder="60"
                                required
                        >
                    </td>

                    <td>
                        <button
                                type="button"
                                class="delete-btn"
                                onclick="deleteRow(this)"
                        >
                            X
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>

            <button type="button" onclick="addRow()">
                Add Set
            </button>

            <button type="submit">
                Submit Workout
            </button>

        </form>
    </div>

    <div>
        <form method="POST" action="add-meal.php">
            <h1>Add Meal:</h1>
            <table>
                <thead>
                <tr>
                    <th>Meal Type</th>
                    <th>Date</th>
                    <th>Calories</th>
                    <th>Fats</th>
                    <th>Carbs</th>
                    <th>Sugars</th>
                    <th>Protein</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <select name="meal_type" required>
                            <option value="">-- Select Meal Type --</option>
                            <option value="breakfast">Breakfast</option>
                            <option value="lunch">Lunch</option>
                            <option value="dinner">Dinner</option>
                            <option value="snack">Snack</option>
                        </select>
                    </td>
                    <td>
                        <input
                                type="date"
                                name="date"
                                required
                        >
                    </td>
                    <td>
                        <input
                                type="number"
                                name="calories"
                                placeholder="500"
                                required
                        >
                    </td>
                    <td>
                        <input
                                type="number"
                                name="fats"
                                placeholder="20"
                                required
                        >
                    </td>
                    <td>
                        <input
                                type="number"
                                name="carbs"
                                placeholder="50"
                                required
                        >
                    </td>
                    <td>
                        <input
                                type="number"
                                name="sugars"
                                placeholder="10"
                                required
                        >
                    </td>
                    <td>
                        <input
                                type="number"
                                name="protein"
                                placeholder="30"
                                required
                        >
                    </td>
                </tr>
                </tbody>
            </table>
            <button type="submit">
                    Submit Meal
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>

<script>
const eventDeltas = <?= $event_deltas_json ?>;
const successData = <?= $success_json ?>;
const distanceData = <?= $distance_json ?>;
const readinessData = <?= $readiness_json ?>;

// --- Best vs. Current: Delta Chart ---
let timeDeltaChart;

function buildDeltaChart(eventKey) {
    const entries = eventDeltas[eventKey] || [];
    const labels  = entries.map(e => e.name);
    const deltas  = entries.map(e => e.delta);
    const colors  = deltas.map(v => v > 0 ? '#e24b4a' : '#5dcaa5');

    if (timeDeltaChart) timeDeltaChart.destroy();

    timeDeltaChart = new Chart(document.getElementById('timeDeltaChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Time delta (s)',
                data: deltas,
                backgroundColor: colors,
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const v = ctx.raw;
                            return (v > 0 ? '+' : '') + v.toFixed(2) + 's from best';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 12 }, color: '#888' }
                },
                y: {
                    grid: { color: 'rgba(0,0,0,0.06)' },
                    ticks: {
                        font: { size: 11 },
                        color: '#888',
                        callback: v => (v > 0 ? '+' : '') + v.toFixed(1) + 's'
                    }
                }
            }
        }
    });
}

//build event tabs from PHP data
const eventKeys     = Object.keys(eventDeltas);
const tabContainer  = document.getElementById('eventTabs');

eventKeys.forEach((ev, i) => {
    const btn = document.createElement('button');
    btn.className   = 'event-tab' + (i === 0 ? ' active' : '');
    btn.textContent = ev;
    btn.addEventListener('click', () => {
        document.querySelectorAll('.event-tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        buildDeltaChart(ev);
    });
    tabContainer.appendChild(btn);
});

if (eventKeys.length > 0) buildDeltaChart(eventKeys[0]);

//Weekly Training Success Score
const successLabels = successData.map(r => r.last_name + ', ' + r.first_name.charAt(0) + '.');
const successScores = successData.map(r => parseFloat(parseFloat(r.average_success_score).toFixed(1)));
const successColors = successScores.map(s => s >= 7 ? '#5dcaa5' : s >= 5 ? '#ef9f27' : '#e24b4a');

new Chart(document.getElementById('successChart'), {
    type: 'bar',
    data: {
        labels: successLabels,
        datasets: [{
            label: 'Avg success score',
            data: successScores,
            backgroundColor: successColors,
            borderRadius: 3,
            borderSkipped: false
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ctx.raw.toFixed(1) + ' / 10' } }
        },
        scales: {
            x: {
                min: 0,
                max: 10,
                grid: { color: 'rgba(0,0,0,0.06)' },
                ticks: { font: { size: 11 }, color: '#888' }
            },
            y: {
                grid: { display: false },
                ticks: { font: { size: 11 }, color: '#888' }
            }
        }
    }
});

//Weekly Training Distance
const distLabels = distanceData.map(r => r.last_name + ', ' + r.first_name.charAt(0) + '.');
const distValues = distanceData.map(r => parseFloat(parseFloat(r.average_distance).toFixed(1)));

new Chart(document.getElementById('distanceChart'), {
    type: 'bar',
    data: {
        labels: distLabels,
        datasets: [{
            label: 'Avg daily miles',
            data: distValues,
            backgroundColor: '#378add',
            borderRadius: 3,
            borderSkipped: false
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ctx.raw.toFixed(1) + ' m/day' } }
        },
        scales: {
            x: {
                min: 0,
                grid: { color: 'rgba(0,0,0,0.06)' },
                ticks: { font: { size: 11 }, color: '#888' }
            },
            y: {
                grid: { display: false },
                ticks: { font: { size: 11 }, color: '#888' }
            }
        }
    }
});

//Training Readiness Indicator
const readinessLabels = ['Red', 'Orange', 'Green'];
const readinessValues = [
    readinessData.R || 0,
    readinessData.O || 0,
    readinessData.G || 0
];

new Chart(document.getElementById('readinessChart'), {
    type: 'doughnut',
    data: {
        labels: readinessLabels,
        datasets: [{
            label: 'Athletes',
            data: readinessValues,
            backgroundColor: ['#e24b4a', '#ef9f27', '#5dcaa5'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ctx.label + ': ' + ctx.raw + ' athlete' + (ctx.raw === 1 ? '' : 's')
                }
            }
        }
    }
});

function addRow() {
    const tbody = document.querySelector("#workoutTable tbody");
    const rowCount = tbody.rows.length + 1;

    const row = document.createElement("tr");

    row.innerHTML = `
        <td>${rowCount}</td>

        <td>
          <input
            type="number"
            name="distance[]"
            placeholder="400"
            required
          >
        </td>

        <td>
          <input
            type="number"
            name="time[]"
            placeholder="60"
            required
          >
        </td>

        <td>
          <button
            type="button"
            class="delete-btn"
            onclick="deleteRow(this)"
          >
            X
          </button>
        </td>
      `;

    tbody.appendChild(row);
}

function deleteRow(button) {
    const row = button.closest("tr");
    row.remove();
    updateSetNumbers();
}

function updateSetNumbers() {
    const rows = document.querySelectorAll("#workoutTable tbody tr");

    rows.forEach((row, index) => {
        row.cells[0].innerText = index + 1;
    });
}
</script>

</body>
</html>
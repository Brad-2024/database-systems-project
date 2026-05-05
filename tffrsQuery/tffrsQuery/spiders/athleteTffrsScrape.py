import re

import scrapy
import mysql.connector
from .spiderHelpers import dateHelper


class AthletetffrsscrapeSpider(scrapy.Spider):
    name = "athleteTffrsScrape"
    allowed_domains = ["www.tfrrs.org"]
    start_urls = ["https://www.tfrrs.org"]

    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.cnx = mysql.connector.connect(
            user="root",
            password="Ninjaman2006",
            host="localhost",
            database="Track"
        )
        self.cursor = self.cnx.cursor()

    def closed(self, reason):
        self.cnx.commit()
        self.cursor.close()
        self.cnx.close()

    async def start(self):
        urls_sql = "SELECT tffrs_url, event, id FROM Athlete WHERE tffrs_url IS NOT NULL"
        self.cursor.execute(urls_sql)
        athletes = self.cursor.fetchall()
        for athlete in athletes:
            url = athlete[0]
            event = athlete[1]
            athlete_id = athlete[2]
            yield scrapy.Request(url=url, callback=self.parse,
                                 meta={"event": event, "athlete_id": athlete_id})

    def parse(self, response):
        event = response.meta["event"]
        athlete_id = response.meta["athlete_id"]
        found_any = False

        tables = response.xpath(
            f'//div[contains(concat(" ", normalize-space(@class), " "), " panel-body ")]'
            f'//table[.//tr/td[normalize-space(.)="{event}"]]'
        )

        if not tables:
            self.logger.info(f"No tables found for athlete ID {athlete_id} and event {event}.")
            return

        for table in tables:
            event_date = table.xpath('.//thead//span/text()').get()
            meet_name = table.xpath('.//thead//a/text()').get()

            if not event_date or not meet_name:
                continue

            insert_date = dateHelper.convert_date(event_date.strip())
            insert_meet_name = clean_text(meet_name)
            meet_id = checkMeetTable(insert_meet_name, insert_date, self.cursor)

            rows = table.xpath(f'.//tr[td[normalize-space(.)="{event}"]]')

            for row in rows:
                race_time = row.xpath('./td[2]//a/text()').get()

                if not race_time:
                    continue

                round_text = row.xpath('normalize-space(./td[3])').get()
                round_match = re.search(r'\(([^)]+)\)', round_text)
                race_round = round_match.group(1) if round_match else None

                race_attributes = {
                    'event': event,
                    'time': race_time.strip(),
                    'meet_id': meet_id,
                    'athlete_id': athlete_id,
                    'round': race_round
                }

                checkRace(self.cursor, race_attributes)
                found_any = True

        self.cnx.commit()

        if not found_any:
            self.logger.info(f"No race rows found for athlete ID {athlete_id} and event {event}.")

def insertRace(cursor, params):
    sql_db = "INSERT INTO Race (event, time, meet_id, athlete_id, round) VALUES (%s, %s, %s, %s, %s)"
    cursor.execute(sql_db, (params['event'], params['time'], params['meet_id'], params['athlete_id'], params['round']))

def checkRace(cursor, params):
    sql_db = "SeLECT id FROM Race WHERE event = %s AND time = %s AND meet_id = %s AND athlete_id = %s AND round = %s"
    cursor.execute(sql_db, (params['event'], params['time'], params['meet_id'], params['athlete_id'], params['round']))
    result = cursor.fetchone()
    if result:
        return
    else:
        insertRace(cursor, params)

def insertMeet(cursor, params):
    sql_db = "INSERT INTO Meet (date, name) VALUES (%s, %s)"
    cursor.execute(sql_db, (params['date'], clean_text(params['name'])))
    meet_id = cursor.lastrowid
    return meet_id

def checkMeetTable(meet_name, meet_date, cursor):
    meet_name = clean_text(meet_name)
    sql_db = "SELECT id FROM Meet WHERE name = %s AND date = %s"
    cursor.execute(sql_db, (meet_name, meet_date))
    result = cursor.fetchone()

    if result:
        meet_id = result[0]
    else:
        meet_attributes = {
            'date' : meet_date,
            'name' : meet_name
        }
        meet_id = insertMeet(cursor, meet_attributes)
    return meet_id

def clean_text(value):
    if value is None:
        return None
    value = value.replace("\xa0", " ")
    return re.sub(r"\s+", " ", value).strip()



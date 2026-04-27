import re


class stateAbbreviation():
    @staticmethod
    def get_state_abbreviation(state_name):
        states_dict = {
            "Alabama": "AL",
            "Alaska": "AK",
            "Arizona": "AZ",
            "Arkansas": "AR",
            "California": "CA",
            "Colorado": "CO",
            "Connecticut": "CT",
            "Delaware": "DE",
            "Florida": "FL",
            "Georgia": "GA",
            "Hawaii": "HI",
            "Idaho": "ID",
            "Illinois": "IL",
            "Indiana": "IN",
            "Iowa": "IA",
            "Kansas": "KS",
            "Kentucky": "KY",
            "Louisiana": "LA",
            "Maine": "ME",
            "Maryland": "MD",
            "Massachusetts": "MA",
            "Michigan": "MI",
            "Minnesota": "MN",
            "Mississippi": "MS",
            "Missouri": "MO",
            "Montana": "MT",
            "Nebraska": "NE",
            "Nevada": "NV",
            "New Hampshire": "NH",
            "New Jersey": "NJ",
            "New Mexico": "NM",
            "New York": "NY",
            "North Carolina": "NC",
            "North Dakota": "ND",
            "Ohio": "OH",
            "Oklahoma": "OK",
            "Oregon": "OR",
            "Pennsylvania": "PA",
            "Rhode Island": "RI",
            "South Carolina": "SC",
            "South Dakota": "SD",
            "Tennessee": "TN",
            "Texas": "TX",
            "Utah": "UT",
            "Vermont": "VT",
            "Virginia": "VA",
            "Washington": "WA",
            "West Virginia": "WV",
            "Wisconsin": "WI",
            "Wyoming": "WY"
        }
        return states_dict.get(state_name)

class maleOrFemale():

    @staticmethod
    def genderAbbreviation(gender):
        gender_dict = {
            "male": "m",
            "female": "f"
        }
        return gender_dict.get(gender)

class dateHelper():

    @staticmethod
    def convert_date(date_str):
        date_dict = {
            "Jan": "01",
            "Feb": "02",
            "Mar": "03",
            "Apr": "04",
            "May": "05",
            "Jun": "06",
            "Jul": "07",
            "Aug": "08",
            "Sep": "09",
            "Oct": "10",
            "Nov": "11",
            "Dec": "12"
        }
        date_str = date_str.replace("\xa0", " ").replace(",", "").strip()
        match = re.search(r'([A-Z][a-z]{2})\s+(\d{1,2})\s*(?:-\s*\d{1,2})?\s+(\d{4})', date_str)

        if not match:
            return ""

        month_text = match.group(1)
        start_day = match.group(2)
        year = match.group(3)

        month = date_dict[month_text]
        day = start_day.zfill(2)

        return f"{year}-{month}-{day}"

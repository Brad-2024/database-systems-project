import scrapy
import mysql.connector
from .spiderHelpers import dateHelper


class AthletetffrsscrapeSpider(scrapy.Spider):
    name = "athleteTffrsScrape"
    allowed_domains = ["www.tfrrs.org"]
    start_urls = ["https://www.tfrrs.org"]

    async def start(self):
        urls = []
        for url in urls:
            yield scrapy.Request(url=url, callback=self.feed,
                                 meta={"event": event, "first_name": first_name, "last_name": last_name})

        cnx = mysql.connector.connection(user='root', password='', host='localhost')
        cursor = cnx.cursor()

    def parse(self, response):
        event = response.meta["event"]
        first_name = response.meta["first_name"]
        last_name = response.meta["last_name"]
        events = response.xpath(
            f'//div[contains(concat(" ", normalize-space(@class), " "), " panel-body ")]'
            f'//table//tr/td[normalize-space(.)="{event}"]/following-sibling::td[1]//a/text()'
        ).getall()

        event_dates = response.xpath(
            f'//div[contains(concat(" ", normalize-space(@class), " "), " panel-body ")]'
            f'//table[.//tr/td[normalize-space(.)="{event}"]]//thead//span/text()'
        ).getall()

        meet_names = response.xpath(
            f'//div[contains(concat(" ", normalize-space(@class), " "), " panel-body ")]'
            f'//table[.//tr/td[normalize-space(.)="{event}"]]//thead//a/text()'
        ).getall()

        if events != [] and event_dates != []:
            for i in range(len(events)):
                insert_event = events[i]
                insert_date = dateHelper.convert_date(event_dates[i])

        else:
            raise ValueError(f"Athlete '{last_name}', '{first_name}' has no entry for event:'{event}'")


def insertRace(cursor, params):
    sql_db = "INSERT INTO Race (event, time, meet_id, athlete_id) VALUES (%s, %s, %s, %s, %s)"
    cursor.execute(sql_db, (params['event'], params['time'], params['meet_id'], params['athlete_id']))

def insertMeet(cursor, params):
    sql_db = "INSERT INTO Meet (date, name, city, state) VALUES (%s, %s, %s, %s)"
    cursor.execute(sql_db, (params['date'], params['name'], params['city'], params['state']))

def checkMeetTable(meet_name, meet_date):
    meet_id = 0
    return meet_id

def insertMeetTable(meet_name, meet_date):
    return



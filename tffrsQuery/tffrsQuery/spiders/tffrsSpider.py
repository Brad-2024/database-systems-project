import scrapy
from .spiderHelpers import stateAbbreviation, maleOrFemale, dateHelper
# example: scrapy crawl tffrsSpider -a team_state="North Carolina" -a team_name="Davidson" -a gender="Male" -a athlete_first_name="Bradley" -a athlete_last_name="Cruthirds" -a events="400H"

class TffrsspiderSpider(scrapy.Spider):
    """
    This spider scrapes the TFRRS website for an athlete's personal records in specified events.
    """
    name = "tffrsSpider"
    allowed_domains = ["www.tfrrs.org"]
    start_urls = ["https://www.tfrrs.org/"]
    url_main = "https://www.tfrrs.org"

    async def start(self):
        team_state = getattr(self, "team_state", None)
        team_name = getattr(self, "team_name", None)
        athlete_gender = getattr(self, "gender", None)
        first_name = getattr(self, "athlete_first_name", None)
        last_name = getattr(self, "athlete_last_name", None)
        events = getattr(self, "events", None)

        if (team_state is None or team_name is None or athlete_gender
                is None or first_name is None or last_name is None
                or events is None ):
        #):
            raise ValueError("Missing required arguments. Please provide team_state, team_name, athlete_gender, athlete_first_name, athlete_last_name, and event.")


        team_state_abrev = stateAbbreviation.get_state_abbreviation(team_state)

        athlete_gender_abrev = maleOrFemale.genderAbbreviation(athlete_gender.lower())

        url = f"https://www.tfrrs.org/teams/tf/{team_state_abrev}_college_{athlete_gender_abrev}_{team_name}.html"
        yield scrapy.Request(url=url, callback=self.feed, meta={"events": events, "first_name": first_name, "last_name": last_name})

    def feed(self, response):
        events = response.meta["events"]
        first_name = response.meta["first_name"]
        last_name = response.meta["last_name"]
        name_urls = response.css('h3:contains("ROSTER") + table.tablesaw tbody tr td a::attr(href)').getall()
        url = ""
        name_compare = first_name + last_name
        for url_suffix in name_urls:
            name_temp = url_suffix.split("/")[-1]
            name = name_temp.split(".")[0]
            url_compare = name.replace("_", "")
            if url_compare == name_compare:
                url = f"https://www.tfrrs.org{url_suffix}"
        if url != "":
            yield scrapy.Request(url=url, callback=self.parse, meta={"events": events, "first_name": first_name, "last_name": last_name})
        else:
            raise ValueError(f"Athlete '{last_name}', '{first_name}' not found on page")

    def parse(self, response):
        events = response.meta["events"]
        events = events.split(",")
        first_name = response.meta["first_name"]
        last_name = response.meta["last_name"]
        for event in events:
            event_pr = response.xpath(
                f'//div[contains(concat(" ", normalize-space(@class), " "), " panel-body ")]'
                f'//table//tr/td[normalize-space(.)="{event}"]/following-sibling::td[1]//a/text()'
            ).getall()

            event_date = response.xpath(
                f'//div[contains(concat(" ", normalize-space(@class), " "), " panel-body ")]'
                f'//table[.//tr/td[normalize-space(.)="{event}"]]//thead//span/text()'
            ).getall()

            meet_names = response.xpath(
                f'//div[contains(concat(" ", normalize-space(@class), " "), " panel-body ")]'
                f'//table[.//tr/td[normalize-space(.)="{event}"]]//thead//a/text()'
            ).getall()

            if event_pr != [] and event_date != []:
                for i in range(len(event_pr)):
                    print(event + ":" + event_pr[i])
                    event_date_new = dateHelper.convert_date(event_date[i])
                    print(event + " date:" + event_date_new)
                    print(meet_names[i])
            else:
                raise ValueError(f"Athlete '{last_name}', '{first_name}' has no entry for event:'{event}'")

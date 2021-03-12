import requests
from bs4 import BeautifulSoup
import time
import csv


def make_request():  # Taken from Pat's example lab6.py file.
    """Makes a request to a given URL."""
    global requests_made
    request = requests.post(site_url, parameters)  # Make the request.
    while request.status_code == 429:  # If being throttled by the server keep trying to make the request.
        time.sleep(30)  # Wait and try again.
        request = requests.post(site_url, data=parameters)
    requests_made += 1  # Keep track of how many requests have been made.
    return BeautifulSoup(request.content, 'html.parser')


def write_csv(name, headings, data):
    """Write an arrow of data rows to a csv file."""
    with open(name, 'w', newline='') as target_file:
        writer = csv.writer(target_file, delimiter=',')
        writer.writerow(headings)
        writer.writerows(data)


site_url = "https://dalonline.dal.ca/PROD/fyskeqiv.P_TransEquiv"  # The url to send requests to.
parameters = {'prov': 'ALLINST'}  # POST parameters to use in requests.
equivalency_id = -1  # The ID to assign to the a course equivalency,

start_time = time.time()  # Store when the scraping started.
completed_inst_count = 0  # How many institutions have been scraped.
requests_made = 0  # How many requests have been made.
equivalencies = []  # All of the equivalencies that have been found.

parsed_response = make_request()  # Make an initial request to the base url.

for province in parsed_response.find("select", {"name": "prov"}).find_all('option')[2:]:  # Loop through all province options, ignoring the blank and unknown options.
    parameters = {'prov': province['value']}  # Set the province POST parameter to the current province.
    parsed_response = make_request()  # Make another request to get pages just for the current province.
    for institution in parsed_response.find("select", {"name": "inst"}).find_all('option')[1:]:  # Go through all institutions in the current province.
        parameters = {'prov': province['value'], 'inst': institution['value']}  # Add the current institution to the POST parameters.
        parsed_response = make_request()  # Make another request to get pages just for the current institution.
        subj_options = parsed_response.find("select", {"name": "subj"})  # Get all subject options for the current institution.
        if subj_options is not None: # If there are courses available for current institution, keep going.
            for subject in subj_options.find_all('option')[1:]:  # Go through all available subjects at the current institution.
                parameters = {'prov': province['value'], 'inst': institution['value'], 'subj': subject['value']}  # Add the current subject to the POST parameters.
                parsed_response = make_request()  # Make another request to get pages just for the current subject at the current institution..
                course_cells = parsed_response.findAll('td', {'class': 'dedefault'})  # Get all table cells in the result page.
                for cell_index in range(0, len(course_cells), 5):  # Go throuhg the cells in groups of 5 (5 cells makes a row).
                    if len(course_cells) - cell_index >= 5:
                        assessed = course_cells[cell_index + 4].text.split(" ") # Get the last assessed year and semester.
                        for dal_course in course_cells[cell_index + 2].get_text(strip=True, separator='|').split('|'):  # Go through all dal courses in the current row.
                            for transfer_course in course_cells[cell_index].get_text(strip=True, separator='|').split('|'):  # Go through all transfer courses in the current row.
                                equivalency_id += 1  # Set the next equivalency ID.
                                equivalencies.append([
                                    equivalency_id,  # The equivalency ID.
                                    province['value'],  # The code for the current province.
                                    province.text,  # The name of the current province.
                                    institution['value'],  # The code for the current institution.
                                    institution.text,  # The name of the current institution.
                                    subject['value'], # The code for the current subject.
                                    subject.text,  # The name of the current subject.
                                    transfer_course,  # The name of the transfer course.
                                    course_cells[cell_index + 1].get_text() if course_cells[cell_index + 1].get_text(strip=True) else 'Unknown',  # The credits for the transfer course.
                                    dal_course,   # The name of the dal course.
                                    course_cells[cell_index + 3].get_text() if course_cells[cell_index + 3].get_text(strip=True) else 'Unknown',  # The credits for the dal course.
                                    assessed[0],  # Last asses year.
                                    assessed[1]  # Last assessed semester.
                                ])
        completed_inst_count += 1
        print("%d institution(s) completed in %.2f second(s) with %d request(s) made" % (completed_inst_count, time.time() - start_time, requests_made))
write_csv('search_engine_db.csv', ['id', 'province_code', 'province_name', 'transfer_inst_code', 'transfer_inst_name', 'subject_code', 'subject_name', 'transfer_inst_course', 'transfer_credits', 'dal_course', 'dal_credits', 'last_assessed_year', 'last_assessed_semester'], equivalencies)
print("\nDone in %.2f second(s) with %d request(s) made." % (time.time() - start_time, requests_made))
print("Found %d course %s." % (len(equivalencies), ('equivalency' if len(equivalencies) == 1 else 'equivalencies')))

<html lang="en">
    <head>
        <title>View All</title>
        <link rel="stylesheet" type="text/css" href="assets/css/bootstrap-4.4.1-dist/css/bootstrap.min.css">
        <meta charset="utf-8">
    </head>
    <body class="container">
        <div class="jumbotron">
            <h1>View Courses</h1>
            <p class="lead">Combine different keywords to find relevant courses. For example 'Newcastle Mathematics'.</p>
            <hr class="my-4">
            <label for="searchInput">Search</label>
            <input class="form-control" type="text" id="searchInput"/>
        </div>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">Institution name</th>
                    <th scope="col">Subject</th>
                    <th scope="col">Course Name</th>
                    <th scope="col">Credits</th>
                    <th scope="col">Dalhousie Equivalent</th>
                    <th scope="col">Credits
                </tr>
            </thead>
            <tbody id="tbody">
            </tbody>
        </table>
        <p id="loadingP">Loading Courses.</p>
        <button style="margin: 0 auto 30px auto;display: none;" class="btn btn-outline-secondary" id="loadBtn">Load More</button>
        <div style="height: 100px;background-color: #e9ecef;width: 100%"></div>

    </body>


    <script src="assets/js/jquery-3.4.1.min.js"></script>
    <script>
        function markup(row) { // Get the html for a single table row.
            return`
                <tr id="course_${row.id}">
                    <td>${row.transfer_inst_name}</td>
                    <td>${row.subject_name}</td>
                    <td>${row.transfer_inst_course}</td>
                    <td>${row.transfer_credits}</td>
                    <td>${row.dal_course}</td>
                    <td>${row.dal_credits}</td>
                </tr>`
        }

        function loadCourses() { // Loads and displays the courses for given query.
            let url = 'api/equivalencies/view.php?'; // The base of the url.
            if ($("#searchInput").val() !== undefined && $("#searchInput").val().trim() !== '') {
                url += `search=${$("#searchInput").val()}&` // If a search term has been given, include it in query string.
            }
            url += `start_index=${start_index}&count=${count}`; // Add on a start index and count to keep track of which results to return.
            $.getJSON(url, function(data) { // Get json from the api.
                $.each(data, function(key, val) { // Go through the returned courses.
                    $('#tbody').append(markup(val)); // Add each course to the table.
                });

                if (data.length < count) { // If there are no more courses available, hide the show more button.
                    $("#loadBtn").css("display", "none");
                } else { // Else keep it visible.
                    $("#loadBtn").css("display", "block");
                }
                start_index += count; // Increment the start index so new results are returned when show m ore is pressed.
            })
        }

        // Initialise start index and count.
        let start_index = 0;
        let count = 10;
        $.when(loadCourses()).done(function () { // Load all courses when the page first loads.
            $('#loadingP').remove();
            $("#loadBtn").css("display", "block"); // Show the show more button.
            $('#loadBtn').on('click', loadCourses); // Set listener to show courses when the button is pressed.
        });

        $("#searchInput").on("input", function () { // Set listener to do a new search when the search query changes.
            start_index = 0; // Reset the start index.
            $('#tbody').html(''); // Empty the table.
            loadCourses(); // Load new courses.
        })
    </script>
</html>

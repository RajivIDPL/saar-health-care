<?php 
include 'header.php'; 

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// SQL query to fetch data from the table
$sql = "SELECT srno, id, stock, buy_range, min_target, stop_loss, ltp, duration, decision FROM stocks";
$result = $con->query($sql);
?>




<style>
    @media (min-width: 1024px) {
    #popup {transform: translate(45%, -500%);}}
    @media (max-width: 1023px) {
    #popup {transform: translate(35%, -500%);}}

    .expandable:hover {
        cursor: pointer;
        background: aliceblue;
    }

    .slider {
            position: fixed;
            top: 0;
            right: -100%;
            width: 300px;
            height: 100%;
            background-color: #f9f9f9;
            box-shadow: -2px 0 5px rgba(0,0,0,0.5);
            transition: right 0.3s ease;
            overflow-y: auto;
            padding: 20px;
            z-index: 1000;
        }
        .slider.open {
            right: 0;
        }

</style>
<body>
    <div class="container-fluid gtco-banner-area">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h1> Here you'll get
                        the best <span>Stock Market Investment Calls</span> for 45 Days free </h1>
                    <p> Started from 01 May to 15 June </p>

                    <a href="pricing.html">Upcoming Pricing<i class="fa fa-angle-right" aria-hidden="true"></i></a>

                    <div class="contain" style="font-size: 35px;font-weight: bold;">
                        <h1 style="font-size: 35px;font-weight: bold;">Event Will Be End In</h1>
                        <div class="countdown" id="countdown"></div>
                    </div>
                    <!-- <a href="#">Contact Us <i class="fa fa-angle-right" aria-hidden="true"></i></a> -->
                </div>
                <div class="col-md-6">
                    <div class="card"><video autoplay loop muted src="images/video.mp4"></video></div>
                </div>
            </div>
        </div>
    </div>

    <div class="slider" id="slider">
        <span id="closePopup" style="float: right; cursor: pointer; color: red; font-size: 24px;">&#10006;</span>
        <h3>More Details</h3>
        <p id="slider-content">Content will be loaded here.</p>
    </div>

    <div style="margin-top: 58px;margin-bottom: 23px; display: flex;justify-content: space-around;">
        <h1 style="font-size: 35px; font-weight: bold;">Stocks Calls</h1>
    </div>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="container-fluid gtco-features" id="about" style="margin-top: 0px; ">
            <div style="overflow-y: scroll;" class="container">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Stock</th>
                            <th scope="col">Buy Range</th>
                            <th scope="col">Min. Target</th>
                            <th scope="col">Stop Loss</th>
                            <th scope="col">LTP</th>
                            <th scope="col">Duration</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        if ($result->num_rows > 0) {
                            // Output data of each row
                            while($row = $result->fetch_assoc()) {
                                echo '<tr class="expandable">
                                    <td style="font-weight: bold;" >' . $row["id"] . '</td>
                                    <td>' . $row["stock"] . '</td>
                                    <td>' . $row["buy_range"] . '</td>
                                    <td>' . $row["min_target"] . '</td>
                                    <td>' . $row["stop_loss"] . '</td>
                                    <td>' . $row["ltp"] . '</td>
                                    <td>' . $row["duration"] . '</td>
                                    <td>' . $row["decision"] . '</td>
                                </tr>';
                            }
                        } else {
                            echo "<tr><td colspan='8'>No records found</td></tr>";
                        }
                        $con->close();
                    ?>

                    </tbody>
                </table>

            </div>
        </div>
        <?php else: ?>
            <div class="container-fluid gtco-features" id="about" style="margin-top: 0px;">
    <div class="container" style = "overflow:hidden;">
        <table id="dataTable" style="filter: blur(10px); background: #00000015;" class="table blurred">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Stock</th>
                    <th scope="col">Buy Range</th>
                    <th scope="col">Min. Target</th>
                    <th scope="col">Stop Loss</th>
                    <th scope="col">LTP</th>
                    <th scope="col">Duration</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">1</th>
                    <td>why don't you login first</td>
                    <td>314-332</td>
                    <td>362</td>
                    <td>286</td>
                    <td>299.45</td>
                    <td>3-4 Weeks</td>
                    <td>Pending</td>
                </tr>
                <tr>
                    <th scope="row">2</th>
                    <td>Ipca Laboratories</td>
                    <td>1374-1440</td>
                    <td>1542</td>
                    <td>1310.55</td>
                    <td>1339.50</td>
                    <td>3-4 Weeks</td>
                    <td>Pending</td>
                </tr>
                <tr>
                    <th scope="row">3</th>
                    <td>don't be fool</td>
                    <td>37-38</td>
                    <td>41.65</td>
                    <td>34.20</td>
                    <td>35.45</td>
                    <td>3-4 Weeks</td>
                    <td>Pending</td>
                </tr>
                <tr>
                    <th scope="row">4</th>
                    <td>is this a joke</td>
                    <td>83-87</td>
                    <td>93.50</td>
                    <td>77.55</td>
                    <td>79.85</td>
                    <td>3-4 Weeks</td>
                    <td>Pending</td>
                </tr>
                <tr>
                    <th scope="row">5</th>
                    <td>you can't get it</td>
                    <td>1701-1754</td>
                    <td>1900</td>
                    <td>1648.55</td>
                    <td>1675</td>
                    <td>3-4 Weeks</td>
                    <td>Pending</td>
                </tr>
                <tr>
                    <th scope="row">6</th>
                    <td>didn't you hear!</td>
                    <td>171-176</td>
                    <td>180</td>
                    <td>163.05</td>
                    <td>170</td>
                    <td>3-4 Weeks</td>
                    <td>Pending</td>
                </tr>
            </tbody>
        </table>
        <div style="position: relative;z-index: 0;" id="popup">
            <button onclick='popUPon()' style="outline:none; padding: 10px 20px;font-size: 16px;cursor: pointer;background-color: #007BFF;color: white;border: none;border-radius: 4px;" id="myButton" class="button-29" role="button">Login First</button>
            <script>
            function popUPon() {
                document.getElementById("myPopup").style.display = "block";
            }
            </script>
        </div>
    </div>
</div>

    <?php endif; ?>



    <div class="container-fluid gtco-features-list">
        <div class="container">
            <div class="row">
                <div class="media col-md-6 col-lg-4">
                    <div class="oval mr-4"><img class="align-self-start" src="images/quality-results.png" alt=""></div>
                    <div class="media-body">
                        <h5 class="mb-0">Quality Results</h5>
                    </div>
                </div>
                <div class="media col-md-6 col-lg-4">
                    <div class="oval mr-4"><img class="align-self-start" src="images/analytic.png" alt=""></div>
                    <div class="media-body">
                        <h5 class="mb-0">Market Analytics</h5>
                    </div>
                </div>
                <div class="media col-md-6 col-lg-4">
                    <div class="oval mr-4"><img class="align-self-start" src="images/affordable-pricing.png" alt="">
                    </div>
                    <div class="media-body">
                        <h5 class="mb-0">Affordable Pricing</h5>
                    </div>
                </div>
                <div class="media col-md-6 col-lg-4">
                    <div class="oval mr-4"><img class="align-self-start" src="images/easy-to-use.png" alt=""></div>
                    <div class="media-body">
                        <h5 class="mb-0">Easy To Use</h5>
                    </div>
                </div>
                <div class="media col-md-6 col-lg-4">
                    <div class="oval mr-4"><img class="align-self-start" src="images/free-support.png" alt=""></div>
                    <div class="media-body">
                        <h5 class="mb-0">Free Support</h5>
                    </div>
                </div>
                <div class="media col-md-6 col-lg-4">
                    <div class="oval mr-4"><img class="align-self-start" src="images/effectively-increase.png" alt="">
                    </div>
                    <div class="media-body">
                        <h5 class="mb-0">Effectively Increase</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="container-fluid" id="gtco-footer">
        <div class="container">
            <div class="row" style="display: flex;justify-content: space-evenly;margin-top: 30px;">

                <div class="col-lg-6">
                    <div class="row">
                        <div class="col-6">

                            <h4 class="mt-5">Follow Us</h4>
                           <ul class="nav follow-us-nav">
                                <li class="nav-item"><a class="nav-link pl-0" href="https://www.facebook.com/profile.php?id=61559193715500"><i class="fa fa-facebook"
                                            aria-hidden="true"></i></a></li>
                                <li class="nav-item"><a class="nav-link" href="https://www.instagram.com/trust_growth24/"><i class="fa fa-instagram"
                                            aria-hidden="true"></i></a></li>
                                <!-- <li class="nav-item"><a class="nav-link" href="#"><i class="fa fa-twitter"
                                            aria-hidden="true"></i></a></li>
                                <li class="nav-item"><a class="nav-link" href="#"><i class="fa fa-linkedin"
                                            aria-hidden="true"></i></a></li> -->
                            </ul>
                        </div>
                        <div class="col-6">
                            <h4>Services</h4>
                            <ul class="nav flex-column services-nav">
                                <li class="nav-item"><a class="nav-link" href="calls.html">Investment Calls</a></li>
                                <li class="nav-item"><a class="nav-link" href="contact.html">Investment Consultancy</a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-12">
                            <p>&copy; 2024. All Rights Reserved. Designed by <a href="https://geniusintellection.com/"
                                    target="_blank">Genius Intellection</a>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>


<script>
    document.querySelectorAll('.expandable').forEach(row => {
        row.addEventListener('click', () => {
            const slider = document.getElementById('slider');
            const content = row.cells[0].innerText + " details...";
            document.getElementById('slider-content').innerText = content;
            slider.classList.toggle('open');
        });
    });

    // Close the slider when clicking outside of it
    document.addEventListener('click', (event) => {
        const slider = document.getElementById('slider');
        if (!slider.contains(event.target) && !event.target.closest('.expandable')) {
            slider.classList.remove('open');
        }
    });
    
    document.getElementById("closePopup").addEventListener("click", function () {document.getElementById('slider').classList.remove('open');});

</script>


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="js/jquery-3.3.1.slim.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <!-- owl carousel js-->
    <script src="owl-carousel/owl.carousel.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        // Set the date we're counting down to
        var launchDate = new Date("June 15, 2024 00:00:00").getTime();

        // Update the countdown every 1 second
        var x = setInterval(function () {

            // Get the current date and time
            var now = new Date().getTime();

            // Find the distance between now and the launch date
            var distance = launchDate - now;

            // Calculate days, hours, minutes and seconds
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Display the countdown result
            document.getElementById("countdown").innerHTML = days + " Days " + hours + " Hrs " + minutes + " Minutes " + seconds + " Sec ";

            // If the countdown is over, display a message
            if (distance < 0) {
                clearInterval(x);
                document.getElementById("countdown").innerHTML = "Event Ended";
            }
        }, 1000);
    </script>
</body>

</html>
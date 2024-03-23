
        </div>
        <!-- End of page content -->

        <!-- Footer -->
        <!--
        <footer class="w3-container w3-center w3-opacity w3-light-grey">
            <p>&copy; 2014 - <?php echo date("Y"); ?> Usage of this site is subject our <a href="/tac.php">terms and conditions</a>. Your data will be handled in accordance with our <a href="/privacy.php">privacy policy</a></p>
        </footer>
        -->

        <script>
            // Used to toggle the menu on small screens when clicking on the menu button
            function navDemoFunction() {
                var x = document.getElementById("navDemo");
                if (x.className.indexOf("w3-show") == -1) {
                    x.className += " w3-show";
                } else {
                    x.className = x.className.replace(" w3-show", "");
                }
            }
            // Used to toggle the dropdown browse menu on small screens menu
            function browseDropdownFunction() {
                var x = document.getElementById("browseDropdown");
                if (x.className.indexOf("w3-show") == -1) {
                    x.className += " w3-show";
                } else {
                    x.className = x.className.replace(" w3-show", "");
                }
            }
            // Used for popup text
            function myPopupFunction() {
                var x = document.getElementById("myPopup");
                if (x.style.display === "none") {
                    x.style.display = "block";
                } else {
                    x.style.display = "none";
                }
            }
        </script>
    </body>
</html>


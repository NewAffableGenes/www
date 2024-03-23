<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";
include $path . "includes/header.php";
include $path . "includes/navbar.php";
// phpinfo();
?>
<div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
    <div class="w3-container w3-white">
        <h2>Privacy Policy</h2>
        <p>Welcome to our website!</p>
        <p>If you continue to browse and use this website, you are agreeing to comply with and be bound by the <a href="/tac.php">terms and conditions</a> of use, which together with our privacy policy (below) govern AffableGenes.com's relationship with you in relation to this website. If you disagree with any part of these terms and conditions or the privacy policy, please do not use our website.</p>
        <p>The term 'AffableGenes' or 'AffableGenes.com' or 'us' or 'we' refers to the owner of the website who may be contacted at <a href="mailto:affablegenes@gmail.com">affablegenes@gmail.com</a>. The term 'you' refers to the user or viewer of our website.</p>
        <p>This privacy policy sets out how AffableGenes.com uses and protects any information that you give when you use this website.</p>
        <p>AffableGenes.com is committed to ensuring that your privacy is protected. Should we ask you to provide certain information by which you can be identified when using this website, then you can be assured that it will only be used in accordance with this privacy statement.</p>
        <p>AffableGenes.com may change this policy from time to time by updating this page. You should check this page from time to time to ensure that you are happy with any changes. This policy is effective from 17th September 2016.</p>
        <h3>What we collect</h3>
        <p>We may collect the following information:</p>
        <ul>
            <li>Name</li>
            <li>Contact information including email address</li>
            <li>Demographic information such as postcode, preferences and interests</li>
            <li>Genealogical and other data that you enter into the website.</li>
        </ul>
        <strong>Important! </strong><p>We do not and cannot check all of the information you store in your tree. It is your responsibility to ensure that you are allowed to record such data on a computer system. For example you must not infringe any third party's rights or privacy by your use of this website.</p>
        <h3>What we do with the information we gather</h3>
        <p>We require this information to understand your needs and provide you with a better service, and in particular for the following reasons:</p>
        <ul>
            <li>Provision of the Family Tree entry, sharing, display and export function</li>
            <li>Internal record keeping</li>
            <li>We may use the information to improve our products and services</li>    
        </ul>
        <p>We will not sell, distribute or lease your personal information to third parties unless required by law to do so.</p>
        <p>You may request details of personal information which we hold about you under the Data Protection Act 1998. A small fee will be payable. If you would like a copy of the information held on you please write to <a href="mailto:affablegenes@gmail.com">affablegenes@gmail.com</a>.</p>
        <h3>Security</h3>
        <p>We are committed to ensuring that your information is secure. In order to prevent unauthorised access or disclosure, we use a professionally administered web hosting service.</p>
        <p>If you believe that any information we are holding on you is incorrect or incomplete, please email us as soon as possible at the above address. We will promptly correct any information found to be incorrect</p>
        <h3>How we use cookies</h3>
        <p>A cookie is a small file which asks permission to be placed on your computer's hard drive. Once you agree, the file is added and the cookie helps analyse web traffic or lets you know when you visit a particular site. Cookies allow web applications to respond to you as an individual. The web application can tailor its operations to your needs, likes and dislikes by gathering and remembering information about your preferences.</p>      
        <p>We use traffic log cookies to identify which pages are being used. This helps us analyse data about webpage traffic and improve our website in order to tailor it to customer needs. We only use this information for statistical analysis purposes and then the data is removed from the system.</p>
        <p>Overall, cookies help us provide you with a better website by enabling us to monitor which pages you find useful and which you do not. A cookie in no way gives us access to your computer or any information about you, other than the data you choose to share with us.</p>
        <p>You can choose to accept or decline cookies. Most web browsers automatically accept cookies, but you can usually modify your browser setting to decline cookies if you prefer. This may prevent you from taking full advantage of the website.</p>
        <h3>Links to other websites</h3>
        <p>Our website may contain links to other websites of interest. However, once you have used these links to leave our site, you should note that we do not have any control over that other website. Therefore, we cannot be responsible for the protection and privacy of any information which you provide whilst visiting such sites and such sites are not governed by this privacy statement. You should exercise caution and look at the privacy statement applicable to the website in question.</p>
    </div>
</div>

<div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
        <a onclick="goBack()" class="w3-button w3-light-grey w3-border w3-block"><b>OK</b></a>
</div>

<script>
    function goBack() {
        window.history.back();
    }
</script>

<?php
include $path . "includes/footer.php";

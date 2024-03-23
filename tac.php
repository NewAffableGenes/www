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
        <h2>Terms & Conditions</h2>
        <p>Welcome to our website!</p>
        <p>If you continue to browse and use this website, you are agreeing to comply with and be bound by the following terms and conditions of use, which together with our privacy policy govern AffableGenes.com's relationship with you in relation to this website. If you disagree with any part of these terms and conditions or the privacy policy, please do not use our website.</p>
        <p>The term 'AffableGenes' or 'AffableGenes.com' or 'us' or 'we' refers to the owner of the website who may be contacted at <a href="mailto:affablegenes@gmail.com">affablegenes@gmail.com</a>. The term 'you' refers to the user or viewer of our website.</p>
        <p>The use of this website is subject to the following terms of use:</p>
        <ul>
            <li>The content of the pages of this website is for your general information and use only. It is subject to change without notice. While we endeavour to keep the information up to date and correct, we make no representations or warranties of any kind, express or implied, about the completeness, accuracy, reliability, suitability or availability with respect to the website or the information, products, services, or related graphics contained on the website for any purpose. Any reliance you place on such information is therefore strictly at your own risk.</li>
            <li>In no event will we be liable for any loss or damage including without limitation, indirect or consequential loss or damage, or any loss or damage whatsoever arising from loss of data or profits arising out of, or in connection with, the use of this website.</li>
            <li>This website uses cookies to monitor browsing preferences. If you do allow cookies to be used, the following personal information may be stored by us for use by third parties: [insert list of information].</li>
            <li>Neither we nor any third parties provide any warranty or guarantee as to the accuracy, timeliness, performance, completeness or suitability of the information and materials found or offered on this website for any particular purpose. You acknowledge that such information and materials may contain inaccuracies or errors and we expressly exclude liability for any such inaccuracies or errors to the fullest extent permitted by law.</li>
            <li>Your use of any information or materials on this website is entirely at your own risk, for which we shall not be liable. It shall be your own responsibility to ensure that any products, services or information available through this website meet your specific requirements.</li>
            <li>This website contains material which is owned by or licensed to us. This material includes, but is not limited to, the design, layout, look, appearance and graphics. Reproduction is prohibited other than in accordance with the copyright notice, which forms part of these terms and conditions.</li>
            <li>All trade marks reproduced in this website which are not the property of, or licensed to, the operator are acknowledged on the website.</li>
            <li>Unauthorised use of this website may give rise to a claim for damages and/or be a criminal offence.</li>
            <li>From time to time this website may also include links to other websites. These links are provided for your convenience to provide further information. They do not signify that we endorse the website(s). We have no responsibility for the content of the linked website(s).</li>
            <li>Your use of this website and any dispute arising out of such use of the website is subject to the laws of England, Northern Ireland, Scotland and Wales.</li>
            <li>Through this website you are able to link to other websites which are not under our control. We have no control over the nature, content and availability of those sites. The inclusion of any links does not necessarily imply a recommendation or endorse the views expressed within them.</li>
            <li>Reasonable effort is made to keep the website up and running smoothly. However, we take no responsibility for, and will not be liable for, the website being unavailable due to technical issues beyond our control.</li>
        </ul>

        <h2>Copyright notice</h2>
        <p>This website and its content is copyright of AffableGenes.com - <p>&copy; 2014 - <?php echo date("Y"); ?> AffableGenes.com. All rights reserved.</p>
        <p>Any redistribution or reproduction of part or all of the contents in any form is prohibited other than the following:</p>
        <ul>
            <li>You may print or download to a local hard disk extracts for your personal and non-commercial use only</li>
            <li>You may copy the content to individual third parties for their personal use, but only if you acknowledge the website as the source of the material</li>
            <li>You may not, except with our express written permission, distribute or commercially exploit the content. Nor may you transmit it or store it in any other website or other form of electronic retrieval system.</li>
        </ul>
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


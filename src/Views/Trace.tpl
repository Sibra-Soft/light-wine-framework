<style type="text/css">
    span.tracecontent b{color:#fff}span.tracecontent{background-color:#fff;color:#000;font:10pt verdana,arial}span.tracecontent table{clear:left;font:10pt verdana,arial;cellspacing:0;cellpadding:0;margin-bottom:25}span.tracecontent tr.subhead{background-color:#ccc}span.tracecontent th{padding:0,3,0,3}span.tracecontent th.alt{background-color:#000;color:#fff;padding:3,3,2,3}span.tracecontent td{color:#000;padding:0,3,0,3;text-align:left}span.tracecontent td.err{color:red}span.tracecontent tr.alt{background-color:#eee}span.tracecontent h1{font:24pt verdana,arial;margin:0,0,0,0}span.tracecontent h2{font:18pt verdana,arial;margin:0,0,0,0}span.tracecontent h3{font:12pt verdana,arial;margin:0,0,0,0}span.tracecontent th a{color:#00008b;font:8pt verdana,arial}span.tracecontent a{color:#00008b;text-decoration:none}span.tracecontent a:hover{color:#00008b;text-decoration:underline}span.tracecontent div.outer{width:90%;margin:15,15,15,15}span.tracecontent table.viewmenu td{background-color:#069;color:#fff;padding:0,5,0,5}span.tracecontent table.viewmenu td.end{padding:0,0,0,0}span.tracecontent table.viewmenu a{color:#fff;font:8pt verdana,arial}span.tracecontent table.viewmenu a:hover{color:#fff;font:8pt verdana,arial}span.tracecontent a.tinylink{color:#00008b;background-color:#000;font:8pt verdana,arial;text-decoration:underline}span.tracecontent a.link{color:#00008b;text-decoration:underline}span.tracecontent div.buffer{padding-top:7;padding-bottom:17}span.tracecontent .small{font:8pt verdana,arial}span.tracecontent table td{padding-right:20}span.tracecontent table td.nopad{padding-right:5}
</style>

<span class="tracecontent" id="__asptrace">
    <table cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;" >
        <tr>
            <th class="alt" align="left" colspan="10">
                <h3>
                    <b>Request Details</b>
                </h3>
                </th>
        </tr>
        <tr align="left">
            <th>Session Id:</th>
            <td>
                <?php echo(session_id()); ?>
            </td>
            <th>Request Type:</th>
            <td>
                <?php echo($_SERVER['REQUEST_METHOD']); ?>
            </td>
        </tr>
        <tr align="left">
            <th>Time of Request:</th>
            <td>
                <?php echo($_SERVER['REQUEST_TIME']); ?>
            </td>
            <th>Status Code:</th>
            <td>
                <?php echo(http_response_code()); ?>
            </td>
        </tr>
        <tr align="left">
            <th>Request Encoding:</th>
            <td>Unicode (UTF-8)</td>
            <th>Response Encoding:</th>
            <td>Unicode (UTF-8)</td>
        </tr>
    </table>

    <!-- Trace Log -->
    <table cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;">
        <tr>
            <th class="alt" align="left" colspan="10">
                <h3>
                    <b>Trace Information</b>
                </h3>
            </th>
        </tr>
        <tr class="subhead" align="left">
            <th>Category</th>
            <th>Message</th>
            <th>From First(s)</th>
            <th>From Last(s)</th>
        </tr>
        <?php
        $i = 0;
        foreach ($GLOBALS['StackTrace'] as $Key => $Value) {
            $Value = explode("#", $Value);

            if($Value[4] == "true"){
                $style = 'style="color:red;"';
            }else{
                $style = '';
            }

            if(($i++ % 2) == 0) {
                echo ('<tr>');
            }else{
                echo ('<tr class="alt" >');
            }
            echo ('<td '.$style.'>'.$Value[0].'</td>');
            echo ('<td '.$style.'>'.$Value[1].'</td>');
            echo ('<td '.$style.'>'.$Value[2].'</td>');
            echo ('<td '.$style.'>'.$Value[3].'</td>');
            echo ('</tr>');
        }
        ?>
    </table>

    <table cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;">
        <tr>
            <th class="alt" align="left" colspan="5">
                <h3>
                    <b>Module Tree</b>
                </h3>
            </th>
        </tr>
        <tr class="subhead" align="left">
            <th>Module UniqueID</th>
            <th>Type</th>
            <th>Render Size Bytes</th>
            <th>Description</th>
            <th>Last Modified</th>
        </tr>
        <?php
        $i = 0;
        foreach ($GLOBALS["CONTROLS"] as $Key => $Value) {
            if(($i++ % 2) == 0) {
                echo ('<tr>');
            }else{
                echo ('<tr class="alt" >');
            }
            echo ('<td>'.$Key.'</td>');
            echo ('<td>'.$Value["Type"].'</td>');
            echo ('<td>'.$Value["RenderSize"].'</td>');
            echo ('<td>'.$Value["Description"].'</td>');
            echo ('<td>'.$Value["LastModified"].'</td>');
            echo ('</tr>');
        }
        ?>
    </table>

    <!-- Session State -->
    <table cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;">
        <tr>
            <th class="alt" align="left" colspan="10">
                <h3>
                    <b>Session State</b>
                </h3>
            </th>
        </tr>
        <tr class="subhead" align="left">
            <th>Session Key</th>
            <th>Type</th>
            <th>Value</th>
        </tr>
        <?php
        $i = 0;
        foreach ($_SESSION as $Key => $Value) {
            if(($i++ % 2) == 0) {
                echo ('<tr>');
            }else{
                echo ('<tr class="alt" >');
            }
            echo ('<td>'.$Key.'</td>');
            echo ('<td>'.gettype($Value).'</td>');
            echo ('<td>'.$Value.'</td>');
            echo ('</tr>');
        }
        ?>
    </table>

    <!-- Included Files -->
    <table cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;">
        <tr>
            <th class="alt" align="left" colspan="10">
                <h3>
                    <b>Includes</b>
                </h3>
            </th>
        </tr>
        <tr class="subhead" align="left">
            <th>Filename</th>
            <th>Render Size Bytes</th>
            <th>File Location</th>
        </tr>
        <?php
        $i = 0;
        foreach (get_included_files() as $Key => $Value) {
            if(($i++ % 2) == 0) {
                echo ('<tr>');
            }else{
                echo ('<tr class="alt" >');
            }
            echo ('<td>'.basename($Value).'</td>');
            echo ('<td>'.filesize($Value).'</td>');
            echo ('<td>'.$Value.'</td>');
            echo ('</tr>');
        }
        ?>
    </table>

    <!-- Request Cookies -->
    <table cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;">
        <tr>
            <th class="alt" align="left" colspan="10">
                <h3>
                    <b>Request Cookies Collection</b>
                </h3>
            </th>
        </tr>
        <tr class="subhead" align="left">
            <th>Name</th>
            <th>Value</th>
            <th>Size</th>
        </tr>
        <?php
        $i = 0;
        foreach ($_COOKIE as $Key => $Value) {
            if(($i++ % 2) == 0) {
                echo ('<tr>');
            }else{
                echo ('<tr class="alt" >');
            }
            echo ('<td>'.$Key.'</td>');
            echo ('<td>'.$Value.'</td>');
            echo ('<td>'.strlen($Value).'</td>');
            echo ('</tr>');
        }
        ?>
    </table>

    <!-- Response Cookies -->
    <table cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;">
        <tr>
            <th class="alt" align="left" colspan="10">
                <h3>
                    <b>Response Cookies Collection</b>
                </h3>
            </th>
        </tr>
        <tr class="subhead" align="left">
            <th>Name</th>
            <th>Value</th>
            <th>Size</th>
        </tr>
    </table>

    <!-- Headers -->
    <table cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;">
        <tr>
            <th class="alt" align="left" colspan="10">
                <h3>
                    <b>Headers Collection</b>
                </h3>
            </th>
        </tr>
        <tr class="subhead" align="left">
            <th>Name</th>
            <th>Value</th>
        </tr>
        <?php
        $i = 0;
        foreach (getallheaders() as $Key => $Value) {
            if(($i++ % 2) == 0) {
                echo ('<tr>');
            }else{
                echo ('<tr class="alt" >');
            }
            echo ('<td>'.$Key.'</td>');
            echo ('<td>'.$Value.'</td>');
            echo ('</tr>');
        }
        ?>
    </table>

    <!-- Reponse Headers -->
    <table cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;">
        <tr>
            <th class="alt" align="left" colspan="10">
                <h3>
                    <b>Response Headers Collection</b>
                </h3>
            </th>
        </tr>
        <tr class="subhead" align="left">
            <th>Name</th>
            <th>Value</th>
        </tr>
        <?php
        foreach ($http_response_header as $Key => $Value) {
            echo ('<tr>');
            echo ('<td>'.$Key.'</td>');
            echo ('<td>'.$Value.'</td>');
            echo ('</tr>');
        }
        ?>
    </table>

    <!-- Form Collection -->
    <table cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;">
        <tr>
            <th class="alt" align="left" colspan="10">
                <h3>
                    <b>Form Collection</b>
                </h3>
            </th>
        </tr>
        <tr class="subhead" align="left">
            <th>Name</th>
            <th>Value</th>
        </tr>
        <?php
        foreach ($_POST as $Key => $Value) {
            echo ('<tr>');
            echo ('<td>'.$Key.'</td>');
            echo ('<td>'.$Value.'</td>');
            echo ('</tr>');
        }
        ?>
    </table>

    <!-- Querystring -->
    <table cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;">
        <tr>
            <th class="alt" align="left" colspan="10">
                <h3>
                    <b>Querystring Collection</b>
                </h3>
            </th>
        </tr>
        <tr class="subhead" align="left">
            <th>Name</th>
            <th>Value</th>
        </tr>
        <?php
            foreach ($_GET as $Key => $Value) {
                echo ('<tr>');
                    echo ('<td>'.$Key.'</td>');
                    echo ('<td>'.$Value.'</td>');
                echo ('</tr>');
            }
        ?>
    </table>

    <!-- Server Variables -->
    <table cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;">
        <tr>
            <th class="alt" align="left" colspan="10">
                <h3>
                    <b>Server Variables</b>
                </h3>
            </th>
        </tr>
        <tr class="subhead" align="left">
            <th>Name</th>
            <th>Value</th>
        </tr>
        <?php
            $i = 0;
            foreach ($_SERVER as $Key => $Value) {
                if(($i++ % 2) == 0) {
                    echo ('<tr>');
                }else{
                    echo ('<tr class="alt" >');
                }
                    echo ('<td>'.$Key.'</td>');
                    echo ('<td>'.$Value.'</td>');
                echo ('</tr>');
            }
        ?>
    </table>
</span>
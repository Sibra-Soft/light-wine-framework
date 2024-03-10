<!DOCTYPE html>
<html>
    <head>
        <title>Compilation Error</title>
        <meta name="viewport" content="width=device-width" />
        <style>
         body {font-family:"Verdana";font-weight:normal;font-size: .7em;color:black;} 
         p {font-family:"Verdana";font-weight:normal;color:black;margin-top: -5px}
         b {font-family:"Verdana";font-weight:bold;color:black;margin-top: -5px}
         H1 { font-family:"Verdana";font-weight:normal;font-size:18pt;color:red }
         H2 { font-family:"Verdana";font-weight:normal;font-size:14pt;color:maroon }
         pre {font-family:"Consolas","Lucida Console",Monospace;font-size:11pt;margin:0;padding:0.5em;line-height:14pt}
         .marker {font-weight: bold; color: black;text-decoration: none;}
         .version {color: gray;}
         .error {margin-bottom: 10px;}
         .expandable { text-decoration:underline; font-weight:bold; color:navy; cursor:pointer;}
         @media screen and (max-width: 639px) {
          pre { width: 440px; overflow: auto; white-space: pre-wrap; word-wrap: break-word;}
         }
         @media screen and (max-width: 479px) {
          pre { width: 280px;}
         }
        </style>
    </head>

    <body bgcolor="white">

            <span><H1>Server Error in '/' Application.<hr width=100% size=1 color=silver></H1>

            <h2> <i>Compilation Error</i> </h2></span>

            <font face="Arial, Helvetica, Geneva, SunSans-Regular, sans-serif ">

            <b> Description: </b>An error occurred during the compilation of a resource required to service this request. Please review the following specific error details and modify your source code appropriately.
            <br><br>

            <b> Compiler Error Message: </b>{{error_message}}<br><br>
<b>Source Error:</b><br><br>
            <table width=100% bgcolor="#ffffcc">
            <tr><td>
            </td></tr>
            <tr>
            <td>
                <code>
                    <pre>{{source}}</pre>
                </code>
            </td>
            </tr>
            </table>

            <br>

            <b>Source File:</b> {{source_file}}
            &nbsp;&nbsp; <b>Line:</b>  {{source_file_line}}

                            <br><br>

            <hr width=100% size=1 color=silver>

            <b>Version Information:</b>&nbsp;Sibra-Soft LightWine Framework Version {{framework_version}}

            </font>

    </body>
</html>
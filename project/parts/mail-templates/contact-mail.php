<?php
return "
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
    <head>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
        <title>A Simple Responsive HTML Email</title>
        <style type='text/css'>
            html, body { background-color: #d8d8d8; font-family: sans-serif;}
            body {margin: 0; padding: 0; min-width: 100%!important;}
            .content {width: 100%; max-width: 600px;}  
        </style>
    </head>
    <body yahoo bgcolor='#f6f8f1'>
        <div style='display:block; width:100%; max-width:600px; text-align:center; margin:0 auto;'>
            <table width='100%' bgcolor='#FFFFFF' border='0' cellpadding='0' cellspacing='0'>
                <tr>
                    <td bgcolor='#000000'>
                        &nbsp;
                    </td>
                </tr>
                <tr>
                    <td bgcolor='#FFFFFF'>
                        <center>
                            <img src='http://almaconsciente.com.mx/wp-content/uploads/2019/01/Logo.png' style='display:block; width:100%; max-width:300px;'>
                        </center>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h1>$body_title</h1>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table width='100%' border='0' cellpadding='0' cellspacing='0'>
                            <tr>
                                <td width='5%'></td>
                                <td style='text-align:left;'>
                                    $body_content
                                </td>
                                <td width='5%'></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td bgcolor='#000000'>
                        &nbsp;
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>
";
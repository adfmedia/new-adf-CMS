<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<!--[if gte mso 9]><xml>
<o:OfficeDocumentSettings>
<o:AllowPNG/>
<o:PixelsPerInch>96</o:PixelsPerInch>
</o:OfficeDocumentSettings>
</xml><![endif]-->
<title>{{ config('app.name') }}</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1.0 " />
<meta name="format-detection" content="telephone=no"/>
<meta name="color-scheme" content="light dark"/>
<meta name="supported-color-schemes" content="light dark"/>

<!--[if !mso]><!-->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,650;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,650;1,700;1,800;1,900&display=swap" rel="stylesheet" />
<!--<![endif]-->
<style type="text/css">
body,td { color:#2f2f2f; font:11px/1.35em "Poppins", sans-serif; }
</style>
<body style="background:#F6F6F6; font-family:'Poppins', sans-serif; font-size:12px; margin:0; padding:0;">
    <div style="background:#F6F6F6; font-family:'Poppins', sans-serif; font-size:12px; margin:0; padding:0;">
        <table cellspacing="0" cellpadding="0" border="0" height="100%" width="100%">
            <tr>
                <td align="center" valign="top" style="padding:20px 0 20px 0">
                    <table bgcolor="FFFFFF" cellspacing="0" cellpadding="10" border="0" width="650" style="border:1px solid #E0E0E0;">
                        <tr>
                            <td valign="top" bgcolor="#170656" align="center" style="background:#170656;text-align: center; padding-bottom: 0;">
                                <a href="{{ url('/') }}">
                                    <img 
                                        src="{{ Vite::asset('resources/images/email/email-header-logo.png') }}" 
                                        alt="{{ config('app.theme_name') }} logo" 
                                        width="334" height="120"
                                        style="margin-bottom:10px;" 
                                        border="0"
                                    />
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#170656" align="center" style="background:#170656; text-align:center;">
                                <h2 style="font-size:24px; line-height:36px; margin:0; color: #ffffff;">@yield('heading')</h2>
                            </td>
                        </tr>
                        <tr>
                            <td valign="top" style="padding: 20px 20px 40px 20px">
                                @yield('content')
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#170656" align="center" style="background:#170656; text-align:center;"><center>
                                <p style="font-size:12px; line-height:16px; margin:0; color: #ffffff;"><a href="{{ url('/') }}" style="color:#ffffff;">{{ url('/') }}</a></p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>
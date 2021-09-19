<?php
/* $Revision: 1.6 $ */
$PageSecurity = 1;

include("includes/session.inc");
require_once 'includes/header_functions.php';

$themeRoot = getThemeRootDirectory();
?>
<html>
    <head>
        <title><?php echo $CompanyName; ?> - <?php echo _('Log Off'); ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=us-ascii" />
        <link rel="stylesheet" href="<?php echo $themeRoot; ?>/default/login.css" type="text/css" />
    </head>

    <body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
        <table bgcolor="#285B86" width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td align="left" valign="top">&nbsp;<br/><br/><br/></td>
            </tr>

            <tr>
                <td align="center" valign="top">

                    <table bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td colspan="2" rowspan="2">
                                <table width="200" border="0" cellpadding="0" cellspacing="0">
                                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" name="loginform" method="post">

                                        <tr>

                                        <table border="0" cellpadding="3" cellspacing="0" width="100%">
                                            <tr>
                                                <td align="center" class="loginText">
                                                    <br /><br /><?php echo _('Thank you for using webERP'); ?><br /><br />
                                                    <?php echo "$CompanyName"; ?>
                                                    <br />
                                                    <a href=" <?php echo $rootpath; ?>/index.php? SID;?>"><b><?php echo _('Click here to Login Again'); ?></b></a>
                                                </td>
                                            </tr>
                                        </table>
                                        </td>
                                        </tr>
                                    </form>
                                </table>

                            </td>
                        </tr>
                    </table>

                </td>
            </tr>
        </table>
    </body>
</html>

<?php
// Cleanup
session_start();
session_unset();
session_destroy();
?>
</body>
</html>



<?php
 
/**
 * Example of products list retrieve using admin account via Magento REST
API. oAuth authorization is used
 */


$callbackUrl = "http://jirafestage.lcgosc.com/stage_1_13/oauth_admin.php";
$temporaryCredentialsRequestUrl = "http://jirafestage.lcgosc.com/stage_1_13/oauth/initiate?oauth_callback=" . urlencode($callbackUrl);
$adminAuthorizationUrl = 'http://jirafestage.lcgosc.com/stage_1_13/admin/oauth_authorize';
$accessTokenRequestUrl = 'http://jirafestage.lcgosc.com/stage_1_13/oauth/token';
$endpoint = "http://jirafestage.lcgosc.com/stage_1_13/api/rest/jirafe_analytics/" . @$_REQUEST['endpoint'] ? @$_REQUEST['endpoint'] : 'map';
$consumerKey = '5s4tvffbhkmpf5s9bkcokf92ia4vp5uw';
$consumerSecret = 'rwslzou2xklcb9gy8s90na1ogoziuve7';
 
session_start();

if (!isset($_GET['oauth_token']) && isset($_SESSION['state']) &&
$_SESSION['state'] == 1) {
   $_SESSION['state'] = 0;
}

$html = "<table border='1' width='100%' cellpadding='5'>";
$html .=  "<tr><td width='200'><strong>Temporary Credentials Request URL</strong></td><td>$temporaryCredentialsRequestUrl</td></tr>";
$html .=  "<tr><td width='200'><strong>Admin Authorization URL</strong></td><td>$adminAuthorizationUrl</td></tr>";
$html .=  "<tr><td width='200'><strong>Access Token Request URL</strong></td><td>$accessTokenRequestUrl</td></tr>";
$html .=  "<tr><td width='200'><strong>REST API Endpoint</strong></td><td>$endpoint</td></tr>";
$html .=  "<tr><td width='200'><strong>Consumer Key</strong></td><td>$consumerKey</td></tr>";
$html .=  "<tr><td width='200'><strong>Consumer Secret</strong></td><td>$consumerSecret</td></tr>";
$html .=  "<tr><td width='200'><strong>Access Token</strong></td><td>" . $_SESSION['token'] . "</td></tr>";
$html .=  "<tr><td width='200'><strong>Access Token Secret</strong></td><td>" . $_SESSION['secret'] . "</td></tr>";
$html .=  "<tr><td colspan='2'><strong>Response</strong></td></tr>";

try {
   $authType = ($_SESSION['state'] == 2) ? OAUTH_AUTH_TYPE_AUTHORIZATION
: OAUTH_AUTH_TYPE_URI;
   $oauthClient = new OAuth($consumerKey, $consumerSecret,
OAUTH_SIG_METHOD_HMACSHA1, $authType);
   $oauthClient->enableDebug();
 
   if (!isset($_GET['oauth_token']) && !$_SESSION['state']) {
       $requestToken =
$oauthClient->getRequestToken($temporaryCredentialsRequestUrl);
       $_SESSION['secret'] = $requestToken['oauth_token_secret'];
       $_SESSION['state'] = 1;
       header('Location: ' . $adminAuthorizationUrl . '?oauth_token=' .
$requestToken['oauth_token']);
       exit;
   } else if ($_SESSION['state'] == 1) {
       $oauthClient->setToken($_GET['oauth_token'], $_SESSION['secret']);
       $accessToken =
$oauthClient->getAccessToken($accessTokenRequestUrl);
       $_SESSION['state'] = 2;
       $_SESSION['token'] = $accessToken['oauth_token'];
       $_SESSION['secret'] = $accessToken['oauth_token_secret'];
       header('Location: ' . $callbackUrl);
       exit;
   } else {
       $oauthClient->setToken($_SESSION['token'], $_SESSION['secret']);
       $oauthClient->fetch($resourceUrl);
       $reply = indent($oauthClient->getLastResponse());
       $html .=  "<tr><td colspan='2'><pre>$reply</pre></td></tr>";
   }
} catch (OAuthException $e) {
    $html .=  "<tr><td colspan='2'>" . json_encode($e) ."</td></tr>";
}

echo  "<html><body>" . $html . "</table></body></html>";


function indent($json) {

    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = '  ';
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;
    for ($i=0; $i<=$strLen; $i++) {
        // Grab the next character in the string.
        $char = substr($json, $i, 1);
        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
            // If this character is the end of an element,
            // output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }
        // Add the character to the result string.
        $result .= $char;
        // If the last character was the beginning of an element,
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
        $prevChar = $char;
    }
    return $result;
}
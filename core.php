<?php
/*

        --{cURL Library}--
   
*/

//File_get_contents alternative with cURL
function file_get_contents_curl($url) 
{
	$ch = curl_init();
	
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
	curl_setopt($ch, CURLOPT_URL, $url);
	
	$data = curl_exec($ch);
	curl_close($ch);
	
	return $data;
}

/*

        --{WebLib}--
    
*/

function getRedirectUrl($url)
{ 
	 $redirect_url = null;

	$url_parts = @parse_url($url);
	if (!$url_parts) return false;
	if (!isset($url_parts['host'])) return false; //can't process relative URLs
	if (!isset($url_parts['path'])) $url_parts['path'] = '/';
	$sock = fsockopen($url_parts['host'], (isset($url_parts['port']) ? (int)$url_parts['port'] : 80), $errno, $errstr, 30);
    if (!$sock) return false;

    $request = "HEAD " . $url_parts['path'] . (isset($url_parts['query']) ? '?'.$url_parts['query'] : '') . " HTTP/1.1\r\n";
    $request .= 'Host: ' . $url_parts['host'] . "\r\n";
    $request .= "Connection: Close\r\n\r\n";
    fwrite($sock, $request);
    $response = '';
    while(!feof($sock)) $response .= fread($sock, 8192);
    fclose($sock);

    if (preg_match('/^Location: (.+?)$/m', $response, $matches)){
	    if ( substr($matches[1], 0, 1) == "/" )
    	    return $url_parts['scheme'] . "://" . $url_parts['host'] . trim($matches[1]);
        else
        	return trim($matches[1]);
        } else {
            return false;
	}
}

function getAllRedirects($url)
{
	$redirects = array();
    	while ($newurl = getRedirectUrl($url)){
        	if (in_array($newurl, $redirects)){
            	break;
            }
            $redirects[] = $newurl;
            $url = $newurl;
    }
	return $redirects;
}

function getFinalRedirect($url)
{
	$redirects = getAllRedirects($url);
    if (count($redirects)>0){
    	return array_pop($redirects);
    } else {
    	return $url;
    }
}

function get_inner_html($string, $start, $end)
{
    $string = " ".$string;
    $pos = strpos($string,$start);
    if ($pos == 0) return "";
    $pos += strlen($start);
    $len = strpos($string,$end,$pos) - $pos;
    return substr($string,$pos,$len);
}
/*

        --{Roblox Library}--
    
*/
function Roblox_ID($data = array())
{
	if (isset($data['ID']))
	{
		$user =  getFinalRedirect('http://www.roblox.com/User.aspx?ID=' . $data['ID']);
        if ($user == 'http://www.roblox.com/Error/DoesntExist.aspx')  
        {
            return false;
        }
        else 
        {
		    $id = str_replace("http://www.roblox.com/User.aspx?ID=", "", $user);
            return $id;
	    }
	}
	elseif (isset($data['name']))
	{
		$user =  getFinalRedirect('http://www.roblox.com/User.aspx?UserName=' . $data['name']);
        if ($user == 'http://www.roblox.com/Error/DoesntExist.aspx') 
        {
            return false;
        }
        else 
        {
	    	$id = str_replace("http://www.roblox.com/User.aspx?ID=", "", $user);
            return $id;
	    }
	}
	else
	{
		return false;
	}
}
    
function Roblox_BlurbReader($id)
{
    $page = file_get_contents_curl('http://www.roblox.com/User.aspx?ID=' . $id);
    libxml_use_internal_errors(true);
    $dom = new DomDocument;
    $dom->loadHTML($page);
    $xpath = new DomXPath($dom);
    $nodes = $xpath->query("//div[@class='UserBlurb']");

    foreach ($nodes as $i => $node) {
		return $node->nodeValue;
    }
}

function Roblox_PublicStats($id)
{
    $page = file_get_contents_curl('http://www.roblox.com/User.aspx?ID=' . $id);
    libxml_use_internal_errors(true);
    $dom = new DomDocument;
    $dom->loadHTML($page);
    $xpath = new DomXPath($dom);
    $nodes = $xpath->query("//span[@id='ctl00_cphRoblox_rbxUserStatisticsPane_lFriendsStatistics']");

    foreach ($nodes as $i => $node) {
        $friends = $node->nodeValue;
    }

    $nodes = $xpath->query("//span[@id='ctl00_cphRoblox_rbxUserStatisticsPane_lForumPostsStatistics']");
    foreach ($nodes as $i => $node) {
        $forum_posts = $node->nodeValue;
    }
    
    $nodes = $xpath->query("//span[@id='ctl00_cphRoblox_rbxUserStatisticsPane_lPlaceVisitsStatistics']");
    foreach ($nodes as $i => $node) {
        $place_visits = $node->nodeValue;
    }
    
    $nodes = $xpath->query("//span[@id='ctl00_cphRoblox_rbxUserStatisticsPane_lKillsStatistics']");
    foreach ($nodes as $i => $node) {
        $knockouts = $node->nodeValue;
    }
    
    $nodes = $xpath->query("//span[@id='ctl00_cphRoblox_rbxUserStatisticsPane_lHighestEverVotingAccuracyStatistics']");
    foreach ($nodes as $i => $node) {
        $voting = $node->nodeValue;
    }
    

    $output = array('ID' => $id, 'Friends' => $friends, 'Forum Posts' => $forum_posts, 'Place Visits' => $place_visits, 'Knockouts' => $knockouts, 'Highest Ever Voting AccuracyStatistics' => $voting.'%');
    return $output; 
}

function Roblox_Status($id)
{
    $page = file_get_contents_curl('http://www.roblox.com/User.aspx?ID=' . $id);
    libxml_use_internal_errors(true);
    $dom = new DomDocument;
    $dom->loadHTML($page);
    $xpath = new DomXPath($dom);
    $nodes = $xpath->query("//span[@id='ctl00_cphRoblox_rbxUserPane_lUserOnlineStatus']");
    $output = array();

    foreach ($nodes as $i => $node) {
        $output = array('Status' => $node->nodeValue);
    }

    $nodes = $xpath->query("//a[@id='ctl00_cphRoblox_rbxUserPane_UserOnlineStatusHyperLink']");
    foreach ($nodes as $i => $node) {
        $output = array('Status' => $node->nodeValue, 'Url' => 'http://roblox.com' . $node->getAttribute('href'));
    }
    return $output;   
}

function Roblox_ListGroups($data = array('id' => '69', 'ally' => true))
{
	if(!isset($data['id']))
	{
		die('ID not suplied');
	}
    $nav = file_get_contents_curl('http://www.roblox.com/Groups/group.aspx?gid='.$data['id']);
    if(isset($data['ally']) and $data['ally'] == true)
	{
		$ally['start'] = '<div id="ctl00_cphRoblox_rbxGroupAlliesPane_RelationshipsUpdatePanel" class="grouprelationshipscontainer">';
        $ally['end'] = '<div style="text-align:center">';
        $nav = get_inner_html($nav, $ally['start'], $ally['end']);
	}
	elseif(isset($data['enemy']) and $data['enemy'] == true)
	{
	    $enemy['start'] = '<div id="ctl00_cphRoblox_rbxGroupEnemiesPane_RelationshipsUpdatePanel" class="grouprelationshipscontainer">';
        $enemy['end'] = '<div style="text-align:center">';
        $nav = get_inner_html($nav, $enemy['start'], $enemy['end']);
	}
    $output = array();
    $GROUP_COUNT = preg_match_all('/_AssetImage1" alt="(.*?)"/', $nav, $GROUP_NAMES);
    $i = 0;
    while ($i<$GROUP_COUNT)
    {
        $NAME_RES = preg_match('/title="'.$GROUP_NAMES[1][$i].'" href="(.*?)"/', $nav, $GROUP_ID);
        array_push($output,array('Name' => $GROUP_NAMES[1][$i], 'ID' => str_replace('Groups/group.aspx?gid=','', $GROUP_ID[1])));
        $i++;
    }
    return $output;
}
?>

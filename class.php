<?php
/*
  ======[ROBLOX  CORE]======
	====[ROBLOX  APIs]====
	  ==[REQUIREMENTS]==
	  	=[SERVER]=
			=> PHP 5.3
			=> Enough bandwidth and storage
	====[ROBLOX QUERY]====
	  ==[REQUIREMENTS]==
		=[SERVER]=
			=> MySQL(i)
			=> PHP 5.3
			=> Enough bandwidth and storage
		=[GAME]=
			=> Lua Query Core [same version as the PHP Query Core!]
			
	  ==[CREDITS]==
		=[PHP]=
			=> Nomis002
		=[LUA]=
			=> /
*/
//Let's make the class.
class Roblox 
{ 
	// Declaring some other functions first, this section is only used in the classes itself.
	protected function file_get_contents_curl($url) 
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
		curl_setopt($ch, CURLOPT_URL, $url);

		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}
	
	protected function getRedirectUrl($url)
	{ 
		$redirect_url = null;

		$url_parts = @parse_url($url);
		if (!$url_parts) return false;
		if (!isset($url_parts['host'])) return false;
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

	protected function getAllRedirects($url)
	{
		$redirects = array();
			while ($newurl = $this->getRedirectUrl($url)){
				if (in_array($newurl, $redirects)){
						break;
				}
				$redirects[] = $newurl;
				$url = $newurl;
			}
		return $redirects;
	}

	protected function getFinalRedirect($url)
	{
		$redirects = $this->getAllRedirects($url);
		if (count($redirects)>0){
				return array_pop($redirects);
		} else {
				return $url;
			}
	}

	protected function get_inner_html($string, $start, $end)
	{
		$string = " ".$string;
		$pos = strpos($string,$start);
		if ($pos == 0) return "";
		$pos += strlen($start);
		$len = strpos($string,$end,$pos) - $pos;	
		return substr($string,$pos,$len);
	}

	//The real class starts here :D!
	
	/*
		Usage:
				$Roblox->ID(array('ID' => '1'))
					=> Used to verify the ID
					
				$Roblox->ID(array('username' => 'ROBLOX'))
	*/
	public function ID($data = array())
	{
		if (isset($data['ID']))
		{
			$user = $this->getFinalRedirect('http://www.roblox.com/User.aspx?ID=' . $data['ID']);
			if ($user == 'http://www.roblox.com/Error/DoesntExist.aspx')  
			{
				// Fatal error: ID is incorrect!
			}
			else 
			{
				return str_replace("http://www.roblox.com/User.aspx?ID=", "", $user);
			}
		}
		elseif (isset($data['username']))
		{
			$user = $this->getFinalRedirect('http://www.roblox.com/User.aspx?UserName=' . $data['username']);
			if ($user == 'http://www.roblox.com/Error/DoesntExist.aspx') 
			{
				// Fatal error: username is incorrect!
			}
			else 
			{
				return str_replace("http://www.roblox.com/User.aspx?ID=", "", $user);
			}
		}
		else
		{
			// Fatal error: some data is not attached!
		}
	}
	/*
		Usage:
				$Roblox->Username('1')
					=> If nothing is returned, the ID has no username attached
	*/	
	public function Username($ID)
	{
		libxml_use_internal_errors(true);
		$dom = new DomDocument;
		$dom->loadHTML($this->file_get_contents_curl('http://www.roblox.com/User.aspx?ID=' . $ID));
		$xpath = new DomXPath($dom);
		$nodes = $xpath->query("//span[@id='ctl00_cphRoblox_rbxUserPane_lUserRobloxURL']");

		foreach ($nodes as $i => $node) {
			return str_replace("'s Profile", '', $node->nodeValue);
		}
	}
	/*
		Usage:
				$Roblox->Blurb('1')
					=> If nothing is returned, the ID has no blurb attached
	*/		
	public function Blurb($ID)
	{
		libxml_use_internal_errors(true);
		$dom = new DomDocument;
		$dom->loadHTML($this->file_get_contents_curl('http://www.roblox.com/User.aspx?ID=' . $ID));
		$xpath = new DomXPath($dom);
		$nodes = $xpath->query("//div[@class='UserBlurb']");

		foreach ($nodes as $i => $node) {
			return $node->nodeValue;
		}
	}
	/*
		Usage:
				$Roblox->Stats('1')
					=> If nothing is returned, the ID has no stats attached
	*/	
	public function Stats($ID)
	{
		libxml_use_internal_errors(true);
		$dom = new DomDocument;
		$dom->loadHTML($this->file_get_contents_curl('http://www.roblox.com/User.aspx?ID=' . $ID));
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
		
		return array('ID' => $ID, 'Friends' => $friends, 'Forum Posts' => $forum_posts, 'Place Visits' => $place_visits, 'Knockouts' => $knockouts, 'Highest Ever Voting AccuracyStatistics' => $voting.'%');
	}
	/*
		Usage:
				$Roblox->Status('1')
					=> If nothing is returned, the ID has no status attached or the account is dead
	*/		
	public function Status($ID)
	{
		libxml_use_internal_errors(true);
		$dom = new DomDocument;
		$dom->loadHTML($this->file_get_contents_curl('http://www.roblox.com/User.aspx?ID=' . $ID));
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
	/*
		Usage:
				$Roblox->GroupList('1')
					=> If nothing is returned, the group has no allies/enemies attached
	*/	
	function GroupList($data = array('ID' => '69', 'ally' => true))
	{
		if(!isset($data['ID']))
		{
			// Fatal error: ID not attached
		}
		$nav = file_get_contents_curl('http://www.roblox.com/Groups/group.aspx?gid='.$data['ID']);
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
}
?>

RobloxPHP
=========

  --{How to use}--
    require_once($_SERVER['DOCUMENT_ROOT'] . '/Cores/core_scripts.php');
    
  --{Credits}--
    nomis002
	
	--{Creator notes}--
	I'm not a pro ^.^ this may include some bad used functions or such, Redirect credit things goes to my brother.
	
	--{Examples}--
    Roblox_ID(array('ID' => 123))    Roblox_ID(array('name' => 'nomis002'))
    		Returns userID

    Roblox_BlurbReader(userID)
    		Returns Roblox blurb
		
    Roblox_PublicStats(userID)
    		Returns the public stats
		
    Roblox_Status(userID)
    		Returns the user status
		
    Roblox_ListGroups(array('id' => '131688', 'ally' => true)) Roblox_ListGroups(array('id' => '131688', 'enemy' => true))
    		Returns the group allies/enemies

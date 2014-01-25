<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/core.php');
 
// Validate and return the user with an ID
if (Roblox_ID(array('ID' => '1') == false))
{
  echo "Wrong ID";
}
else
{
  echo json_encode(Roblox_ID(array('ID' => '1')));
}

//Validate and return the user with a name
if (Roblox_ID(array('name' => 'roblox') == false))
{
  echo "Wrong name";
}
else
{
  echo json_encode(Roblox_ID(array('name' => 'roblox')));
}

//Get an user his/her blurb
echo json_encode(Roblox_BlurbReader('1'));

//Get an user his/her public stats
echo json_encode(Roblox_PublicStats('1'));

//Get an user his/her status
echo json_encode(Roblox_Status('1'));

//Get a group it's allies/enemies
echo json_encode(Roblox_ListGroups(array('id' => '131688', 'ally' => true)));
echo json_encode(Roblox_ListGroups(array('id' => '131688', 'enemy' => true)));
?>

PHP Library for accessing Drop.io's API.  
============================
This library requires to you sign up for an API key. To get an API key, go to [http://api.drop.io/](http://api.drop.io/).

Set the api key to use used by the library.

	Dropio_Api::setKey(DROPIO_API_KEY);

Create a drop and add an asset.

	$drop = new Dropio_Drop();
	$drop->description = 'Drop for Dropapalooza';
	$drop->save();
  
	//Add an asset
	$asset = $drop->addFile(PATH_TO_FILE);
  
	//Get the public url for the drop.
	echo 'Drop: http://drop.io/'.$drop->name;
  
Add an asset to a pre-existing drop.
  
	$drop = Dropio_Drop::load(DROP_NAME);
	#drop->addFile(PATH_TO_FILE);
  


Matt's Display of Drupal Concepts
---------------------

This repo has basic implementations of concepts loosely based on things I've had to do in the past.

###  Concepts portrayed
- Extending the node class
- Using the Ajax Api
- Making a webform handler
- Generating content types and fields on module install
- Using routes and controllers
- Importing CSVs and converting them to nodes
- Batch Processing
- Twig Templating
- Block Plugins
- Grabbing data from logged in user
- Configuration Forms

### Weather Module
**Located in /modules/custom/weather**
#### Overview
This module allows users to add multiple locations with their IP, zipcode, or manually enter the Lat and Long, and adds a page that displays weather data associated with each of those locations.

I decided to play with the idea of removing the concept of a submit button. All changes done to the locations on the location entry page make live edits to the fields stored on the actual nodes. When clicking 'add location' on the form a node is created with a latitude and longitude based on the user's IP address.

#### APIs Used
[OpenWeatherMap](https://openweathermap.org/ "OpenWeatherMap") Used to obtain weather data based on lat and long. Chosen because it was free and straightforward to use

[Google Maps/Geocoding](https://developers.google.com/maps/documentation/geocoding/overview "Google Maps/Geocoding") Used to convert zip codes into coordinates. Chosen because I may want to use more of its features like generating a google map with the data later.

[GeoIPLookup](https://geoiplookup.io/ "GeoIPLookup") Used to get Lat and Long from the user's IP to be used as default values in new locations. Chosen because it is free and does not require an api key.

#### Routes
/weather-entry Place where users can add and remove locations
/weather Place where you can view every location and its weather. The weather is updated whenever this page is visited
/admin/config/weather/settings Area for entering API keys

#### Wishlist
Things to add to the weather module if time allows
* Generate the location content type and its associated field on install of the module
* Display a google map with pins of all the locations
* Display more weather data besides temperature like wind and cloudiness
* Add more error checking
* Use twig template for weather data page with some basic styling
* Improve feel of weather entry database
### User Greeting Module
**Located in /modules/custom/user_greeting**

The user greeting module adds a block plugin that displays last login time and greets the user.
Add it to the sidebar region in the block layout editor and edit the greeting (optional) at /admin/config/user-greeting/settings

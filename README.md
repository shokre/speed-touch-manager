speed-touch-manager
===================

PHP class for controling speed touch router


## Usage

### constructor

```php
$stm = new SpeedTouchManager('router.ip', 'username', 'password');
```

### listGames

List Game and Application Sharing assignments

```php
$stm->listGames();
```
result:

```php
...
[57] => Array
        (
            [game] => Lotus Notes
            [assignment] =>  
            [mode] => Server
        )

[58] => Array
        (
            [game] => Mail Server (SMTP)
            [assignment] => Unknown-ff-ff-ff-ff-ff-ff
            [mode] => Server
        )
...
```
### create a game

Game is a service in speed-touch language

```php
$stm->createGame('git');
$stm->assignPorts('git', SpeedTouchManager::PROTO_TCP, 9418);
```

### assign game to network device
```php
$stm->assignGame($srv, '192.168.1.1');
```

### unassign game
```php
$stm->unassignGame($srv, '192.168.1.1');
```
### delete game

game must be unassigned first

```php
$stm->deleteGame($srv, '192.168.1.1');
```

### configure WLAN
```php
$stm->configWLAN(
	true,                             // Interface Enabled
	'your ssid',                      // Network Name (SSID)
	SpeedTouchManager::TYPE_802_11bg, // Interface Type
	6,                                // Channel
	true,                             // Allow multicast from Broadband Network
	true,                             // Broadcast Network Name
	SpeedTouchManager::ALLOW_AUTO,    // Allow New Devices
	SpeedTouchManager::ENC_WPA,       // Security Mode
	'wpa_psk', 						  // WPA-PSK Preshared Key
	SpeedTouchManager::WPA_WPA2);     // WPA-PSK Version
```

## Testing

Tested on:
- SpeedTouch THOMSON ST780 (7.4.35.2)
- SpeedTouch THOMSON TG782 (8.6.Q.3)

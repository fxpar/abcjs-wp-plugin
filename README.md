# abcjs-wp-plugin
Adaptation of Paul Rosen's abcjs plugin version for wordpress.

Different way I tried to include the features for the wonderful library abcjs.

# Features

#### abc-notation.php
* [x] add instrument
* [x] Editor: create div from shortcode abc

#### abcjs-basic-min.js
* [x] include ukulele tuning GCEA (replaced viola)

# Editor shortcode
Current version works very well with editor shortcode.
````
[abcjs-editor  options="{ responsive: 'resize', tablature: &#91;{instrument: 'violin' }&#93;}"  ]
X: 1
T: Cooley's
M: 4/4
L: 1/8
R: reel
K: Em
V: Melody
|:D2|EB{c}BA B2 EB|~B2 AB dBAG|FDAD BDAD|FDAD dAFD|
EBBA B2 EB|B2 AB defg|afe^c dBAF|DEFD E2:|
|:gf|eB B2 efge|eB B2 gedB|A2 FA DAFA|A2 FA defg|
eB B2 eBgB|eB B2 defg|afe^c dBAF|DEFD E2:|
[/abcjs-editor]
````

# Audio shortcode
Current version buggy for the audio shortcode.
````
[abcjs-audio class-audio="abcjs-audio" animate="true" params="{ responsive: 'resize', tablature: &#91;{instrument: 'viola' }&#93;  }"  ]
X: 1
T: Cooley's
M: 4/4
L: 1/8
R: reel
K: Em
V: Melody
|:D2|EB{c}BA B2 EB|~B2 AB dBAG|FDAD BDAD|FDAD dAFD|
EBBA B2 EB|B2 AB defg|afe^c dBAF|DEFD E2:|
|:gf|eB B2 efge|eB B2 gedB|A2 FA DAFA|A2 FA defg|
eB B2 eBgB|eB B2 defg|afe^c dBAF|DEFD E2:|
[/abcjs-audio]
````

# Ukulele tabs

Requested a real and correct inclusion of ukulele (GCEA) and ukulele low G (gCEA) : https://github.com/paulrosen/abcjs/issues/1103

````
 viola: {
     name: "StringTab",
     defaultTuning: ["G,","C","E","A"],
     isTabBig: !1,
     tabSymbolOffset: 0
 },
````

# abcjs-wp-plugin
Adaptation of Paul Rosen's abcjs plugin version for wordpress.

Different way I tried to include the features for the wonderful library abcjs.

# Features

* [x] add transpose
* [ ] Editor: create div from shortcode abc

# Editor shortcode
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


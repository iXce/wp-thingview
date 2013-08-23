wp-thingview
============
A simple STL viewer integrated with WordPress media upload system.

Usage
-----
Upload a STL using WordPress media system, hit `Insert into post` and you'll
get a nice `[thing]` shortcode, which will produce a nice object viewer.

The `[thing]` tag supports several parameters:
- `width`: the width of the viewer in pixels (default 500px)
- `height`: the height of the viewer in pixels (default 360px)
- `class`: classes to apply to the viewer container (default '')
- `color`: color of the rendered object (default #86E4FF)
- `background`: background color for the viewer (default inherit)
- `border`: border around the viewer container (default none)
- `gridsize`: size of the base plane, in object units (default 200)
- `gridunit`: distance between plane lines, in object units (default 10)

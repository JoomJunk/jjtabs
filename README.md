JJ Tabs
======

This module is still a beta - it is not available for public download on JoomJunk yet!!

JoomJunk Tabs - A PHP/jQuery tabs system designed for use on Joomla sites

Please see the changelog for detailed information about recent changes

Instructions
======
Start the tabs with:
{JJTabs Start}

To create a panel:
{JJTabs Panel|title=title name}

To end the tab:
{JJTabs End}

To pass a color parmeter for a tab simply add a comma after the title for example:
{JJTabs Panel|title:title name,color:red}

Parameters accepted in the start code are:
useClick - whether the tab should change on click or on hover
useCookie - Whether to use cookies to remember the tab

Parameters accepted in the panel code are:
title - the title of the tab (COMPULSORY)
color - the color of the tab (defaults to the plugin parameter)

window.onload = function()
{
    /* Source code box expanding when horizontal overflow */

    var pres = document.getElementsByTagName('pre');
	var pres_len = pres.length;
	for (i = 0; i < pres_len; i++)
    {
		if ( "highlight" === pres[i].parentNode.className )
        {
            if ( pres[i].parentNode.scrollWidth <= pres[i].parentNode.clientWidth )
            {
                continue;
            }

            pres[i].onmouseover = (function()
            {
                var original_parent_width = this.parentNode.style.width;

                var mouseover = function()
                {
                    this.parentNode.style.width = this.parentNode.scrollWidth + 'px';
                    this.onmouseover = function(){};
                }
                this.onmouseout = function()
                {
                    this.parentNode.style.width = original_parent_width;
                    this.onmouseover = mouseover;
                }

                return mouseover;
            }.call( pres[i] ));
        }
	}

    var links = document.getElementsByTagName('a');
    var links_len = links.length;
    var submenus = [];
    var hide_timeouts = [];

    for (i = 0; i < links_len; i++)
    {
		if ( "submenu_title" === links[i].className )
        {
            links[i].onmouseover = (function( index )
            {
                var submenu = this;

                do {
                    submenu = submenu.nextSibling;
                } while ( submenu && submenu.nodeType != 1 && 'submenu' != submenu.className );

                if ( !submenu || submenu == this )
                {
                    // No submenu found, the we retunr empty function.
                    return function(){};
                }

                var hide_submenu = function() { submenu.style.display = 'none' };
                submenus.push[submenu];

                submenu.onmouseover = function()
                {
                    if ( !hide_timeouts[index] ) return;
                    clearTimeout( hide_timeouts[index] );
                }
                submenu.onmouseout = function()
                {
                    hide_timeouts[index] = setTimeout( hide_submenu, 250 );
                }
                this.onmouseout = function()
                {
                    hide_timeouts[index] = setTimeout( hide_submenu, 250 );
                }

                return function() {
                    if ( hide_timeouts[index] )
                    {
                        clearTimeout( hide_timeouts[index] );
                    }

                    submenu.style.display = 'block';
                    // Hiding all the others.
                    for( var j=0; j < submenus.length; j++ )
                    {
                        if ( !submenus[j] || submenus[j] == submenu ) continue;
                        submenus[j].style.display = 'none';
                    }
                    hide_timeout[index] = setTimeout( function(){

                    } );
                };
            }.call( links[i], i ));
        }
    }
}
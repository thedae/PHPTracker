window.onload = function()
{
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

            pres[i].onmouseover = function()
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

                mouseover.call(this);
            }
        }
	}
}
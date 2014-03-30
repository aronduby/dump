(function(){

	// alternative to DOMContentLoaded
	document.onreadystatechange = function () {
		if (document.readyState == "interactive") {


			// make sure we cancel events from ul's so they don't bubble up to the expandable
			var nodes = document.querySelectorAll('ul.d-node');
			for(var i=0; i < nodes.length; ++i){
				nodes[i].addEventListener('click', function(e){
					e.stopPropagation();
					e.preventDefault();
				});
			}

			// expand subs
			var expanders = document.querySelectorAll('.d-expandable');
			for(var i = 0; i < expanders.length; ++i) {
	  			expanders[i].addEventListener('click', function(e){
	  				var cl = this.classList;
	  				if(cl.contains('d-expandable')){
	  					cl.toggle('d-open');
	  					e.stopPropagation();
	  					e.preventDefault();
	  				}
	  			});
			}

			// expand/collapse all
			nodeListLoop(document.querySelectorAll('.d-toggle-all'), function(i){
				this.addEventListener('click', function(e){
					var wrapper = this.parentNode.parentNode,
						expanded = wrapper.getAttribute('data-expanded')=='true' ? true : false;

					if(expanded === true){
						nodeListLoop(wrapper.querySelectorAll('.d-open'), function(){
							this.classList.remove('d-open');
						});
						wrapper.setAttribute('data-expanded','false');
					} else {
						nodeListLoop(wrapper.querySelectorAll('.d-expandable'), function(){
							this.classList.add('d-open');
						});
						wrapper.setAttribute('data-expanded','true');
					}

					e.stopPropagation();
					e.preventDefault();
				});
			});

	  	}
	}

	function nodeListLoop(nl, cb){
		for(var i=0; i < nl.length; ++i){
			cb.call(nl[i], i);
		}
	}
})()
;(function ( $, window, document, undefined ) {

	var _name = 'kit'
	,	_defaults = {
			enabled: true
		,	animation: null
		,	animationDuration: 0
		,	theme: 'dark'
		}

	$.kit = function(element, options) {
		this.name = _name;
		this.state = 'uninitialized'; // closed in opened out
		
		this.element = element;
		this.options = $.extend({}, _defaults, options);
		this.El = $(this.options.template);

		// this._setOptions( options );
	}

	$.kit.prototype._setOptions = function( options_ ) {
		var me  = this;
		var $me = $(me.element);

		$.each( options_, function( key_, value_ ) {
			me._setOption( key_, value_ );
			me.__setOption( key_, value_ );

			var currentOption = { };
				currentOption[ key_ ] = value_;

			if( $.isFunction( value_ ) ) {
				me._on( $me, currentOption );
			}

		});
	}

	$.kit.prototype._setOption = function( key_, value_ ) {
		var me  = this;
		var $me = $(me.element);

		switch( key_ ) {
			case 'theme':
				me.El.removeClass( '-' + me.options.theme + '-' );
				me.El.addClass( '-' + value_ + '-' )
			break;

			case 'enabled':
				value_ === true ? me.El.removeClass( '-disabled-' ) : me.El.addClass( '-disabled-' );
			break;
		}
	}

	$.kit.prototype.__setOption = function ( key_, value_ ) {
		this.options[ key_ ] = value_;
	}

	$.kit.prototype._on = function( element_, handlers_ ) {
		var me = this;

		$.each( handlers_, function( event, handler ) {
			element_.bind( event + '.' + me.name, handler );
		});
	}

	$.kit.prototype.enable = function() {
		var me = this;

		me._setOption( 'enabled', true )
	}

	$.kit.prototype.disable = function() {
		var me = this;

		me._setOption( 'enabled', false )
	}

	$.kit.prototype.getState = function() {
		return this.state;
	}

	$.kit.prototype._getOtherInstanses = function( instanses_ ) {
		var me = this;
		
		return $.grep( instanses_ , function( el ) {
			return $.data($(el[0]), 'kit-' + me.name) !== me;
		});
		
	}

	$.kit.prototype._removeInstanse = function( instanses_ ) {
		var me = this;
		
		var what
		,	a = arguments.splice(0,1)
		,	L = a.length
		,	ax;

		while( L && instanses_.length ) {
			what = a[ --L ];
			
			while( (ax = instanses_.indexOf( what ) ) != -1 ){
				instanses_.splice( ax, 1 );
			}
		}

		return me;
	}

})( jQuery, window, document );
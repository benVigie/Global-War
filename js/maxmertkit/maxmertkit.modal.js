;(function ( $, window, document, undefined ) {
	var _name = 'modal'
	,	_defaults = {
			autoOpen: false
		,	onlyOne: true
		,	shaderClass: '-shader'
		,	closerClass: '-closer'

		,	beforeOpen: $.noop()
		,	open: $.noop()
		,	ifOpenedOrNot: $.noop()
		,	ifNotOpened: $.noop()
		,	beforeClose: $.noop()
		,	close: $.noop()
		,	ifClosedOrNot: $.noop()
		,	ifNotClosed: $.noop()
	}

	Modal = function( element_, options_ ) {
		this.element = element_;
		this.name = _name;
		this.options = $.extend( {}, this.options, _defaults, options_ );
		this._setOptions( this.options );

		if( typeof $.modal === 'undefined' )
			$.modal = [];
		if( this.element !== 'undefined' )
			$.modal.push( this.element );

		$(this.element).css({
			display: 'none'
		,	left: '50%'
		,	top: '50%'
		,	position: 'fixed'
		});

		var me = this;
		$(this.element).find( '.' + me.options.closerClass ).on( 'click', function() {
			me.close();
		})

		this.init();
	}

	Modal.prototype = new $.kit();
	Modal.prototype.constructor = Modal;

	Modal.prototype.__setOption = function( key_, value_ ) {
		var me  = this
		,	$me = $(me.element);
		
		switch( key_ ) {
			case 'animation':
				if( $.easing === undefined || !(value_ in $.easing) )
					switch( value_ ) {
						case 'scaleIn':
							$me.addClass('-mx-scaleIn');
						break;

						case 'growUp':
							$me.addClass('-mx-growUp');
						break;

						case 'rotateIn':
							$me.addClass('-mx-rotateIn');
						break;

						case 'dropIn':
							$me.addClass('-mx-dropIn');
						break;
					}

			break;

			case 'theme':
				$me.addClass( '-' + value_ + '-' );
			break;
		}

		me.options[ key_ ] = value_;
	}

	Modal.prototype.init = function() {
		var me  = this;

		if( me.options.autoOpen )
			me.open();
	}

	Modal.prototype._setPosition = function() {
		var me  = this
		,	$me = $(me.element)
		,	width = $me.outerWidth()
		,	height = $me.outerHeight();

		$me.css({
			marginLeft: Math.round(-width / 2),
			marginTop: Math.round(-height / 2)
		});
	}

	Modal.prototype.open = function() {
		var me  = this
		,	$me = $(me.element);

		if( me.options.enabled === true && me.state !== 'opened' ) {
			
			me._openShader();
			me.state = 'in';

			if( me.options.beforeOpen !== 'undefined' && (typeof me.options.beforeOpen === 'object' || typeof me.options.beforeOpen === 'function' )) {
				
				try {
					var deferred = me.options.beforeOpen.call( $me );
					deferred
						.done(function(){
							me._open();
						})
						.fail(function(){
							me._closeShader();
							me.state = 'closed';
							$me.trigger('ifNotOpened.' + me.name);
							$me.trigger('ifOpenedOrNot.' + me.name);
						})
				} catch( e ) {
					me._open();
				}
				
			}
			else {
				me._open();
			}
		}
	}

	Modal.prototype._open = function() {
		var me  = this;
		var $me = $(me.element);

		if( me.state === 'in' ) {
			
			if( me.options.onlyOne )
				
				$.each( me._getOtherInstanses( $.modal ), function() {
					if( $.data( this, 'kit-' + me.name ).getState() === 'opened' )
						$.data( this, 'kit-' + me.name ).close();
				});

			me._setPosition();
			
			if( me.options.animation !== null && me.options.animation !== false )
			{	
				me._openAnimation();
			}
			else
			{
				$me.css({opacity:1}).show();
			}
			
			me.state = 'opened';
			$me.trigger('open.' + me.name);
		}

		$me.trigger('ifOpenedOrNot.' + me.name);
	}

	Modal.prototype._openAnimation = function() {
		var me  = this;
		var $me = $(me.element);

		$('html').addClass( '-mx-shader' );

		if( $.easing !== 'undefined' && (me.options.animation.split(/[ ,]+/)[1] in $.easing || me.options.animation.split(/[ ,]+/)[0] in $.easing) ) {
			me.element.css({opacity:1}).slideDown({
				duration: me.options.animationDuration,
				easing: me.options.animation.split(/[ ,]+/)[0]
			});
		}
		else {
			$me.show();
			$me.addClass('-mx-start');
		}
	}

	Modal.prototype.close = function() {
		var me  = this;
		var $me = $(me.element);

		if( me.options.enabled === true && me.state !== 'closed' ) {

			me.state = 'out';

			if( me.options.beforeClose != 'undefined' && (typeof me.options.beforeClose === 'object' || typeof me.options.beforeClose === 'function' ))
			{
				
				try {
					var deferred = me.options.beforeClose.call( $me );
					deferred
						.done(function(){
							me._close();
						})
						.fail(function(){
							me._closeShader();
							$me.trigger('ifNotClosed.' + me.name);
							$me.trigger('ifClosedOrNot.' + me.name);
							me.state = 'opened';
						})
				} catch( e ) {
					me._close();
				}
				
			}
			else {
				me._close();
			}
		}
	}

	Modal.prototype._close = function() {
		var me  = this;
		var $me = $(me.element);

		me._closeShader();
		if( me.state === 'out' ) {
			
			if( me.options.animation === null )
				$me.hide();
			else {
				me._closeAnimation();
			}
			me.state = 'closed';

			$me.trigger('close');	
		}
		
		$me.trigger('ifClosedOrNot.' + me.name);
	}

	Modal.prototype._closeAnimation = function() {
		var me  = this;
		var $me = $(me.element);

		$('html').removeClass( '-mx-shader' );

		if( $.easing !== 'undefined' && (me.options.animation.split(/[ ,]+/)[1] in $.easing || me.options.animation.split(/[ ,]+/)[0] in $.easing) ) {
			$me.slideUp({
				duration: me.options.animationDuration,
				easing: me.options.animation.split(/[ ,]+/)[1] !== 'undefined' ? me.options.animation.split(/[ ,]+/)[1] : me.options.animation
			});
		}
		else {
			$me.removeClass('-mx-start');
			$me.hide();
		}
	}

	Modal.prototype._openShader = function() {
		var me  = this
		,	$me = $(me.element);

		if( $.shader === undefined ) {
			$.shader = $('<div class="' + me.options.shaderClass + '"></div>');
			$('body').append( $.shader );
		}

		if( me.options.animation !== null || me.options.animation !== undefined )
			$.shader.fadeIn(150);
		else
			$.shader.css({ opacity: 1 }).show();

	}

	Modal.prototype._closeShader = function() {
		var me  = this
		,	$me = $(me.element);

		if( $.shader === undefined ) {
			$.shader = '<div class="' + me.options.shaderClass + '"></div>';
			$(document).append( $.shader );
		}

		if( me.options.animation !== null || me.options.animation !== undefined )
			$.shader.fadeOut(350);
		else
			$.shader.css({ opacity: 0 }).hide();

	}

	$.fn[_name] = function( options_ ) {
		return this.each(function() {
			if( ! $.data( this, 'kit-' + _name ) ) {
				$.data( this, 'kit-' + _name, new Modal( this, options_ ) );
			}
			else {
				typeof options_ === 'object' ? $.data( this, 'kit-' + _name )._setOptions( options_ ) :
					typeof options_ === 'string' && options_.charAt(0) !== '_' ? $.data( this, 'kit-' + _name )[ options_ ] : $.error( 'What do you want to do?' );
			}
		});
	}

})( jQuery, window, document );
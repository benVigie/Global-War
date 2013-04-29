;(function ( $, window, document, undefined ) {

	var _name = 'notify'
	,	_defaults = {
			enabled: true
		,	theme: 'dark'

		,	template: '<div class="-notify-container js-notify-container"></div>'
		,	templateNotify: '<div class="-notify -mx-releaseIn _bottom_"></div>'
		,	templateNotifyHeader: '<div class="-notify-header"></div>'
		,	templateNotifyContent: '<div class="-notify-content"></div>'
		,	templateCloser: '<div class="-notify-closeAll -label -dark-">close all <i class="-icon-remove-circle -icon-light-"></i></div>'
		}

	Notify = function(message, options) {
		var me = this;

		if( this.state === 'uninitialized' ) {
			this.name = _name;
			this._id = 0;
			this.notifications = [];
			this.archive = [];

			this.options = options === undefined ? 
								$.extend({}, _defaults, message) :
								$.extend({}, _defaults, options);

			this.$element = $('<div class="-notify-container js-notify-container"></div>');
			$('body').append( this.$element );
			this.closer = $(this.options.templateCloser)//$element.find('.-notify-closeAll');
			
			this.closer.on('click', function( event_ ){
				var i = 0;
				while( me.notifications.length !== 0 ){
					clearTimeout( me.notifications[i].timer );
					me.notifyClose( me.notifications[i] );
				}
			});

			this.$element.append( this.closer );
		}
		
		typeof options === 'object' && this._setOptions( options );
		typeof message === 'string' && this.notify( message, options );
		// options === undefined && this.showNotificationCenter();
		
	}

	Notify.prototype = new $.kit();
	Notify.prototype.constructor = Notify;


	Notify.prototype.__setOption = function( key_, value_ ) {
		var me  = this;
		var $me = me.$element;

		switch( key_ ) {
			case 'theme':
				$me.removeClass( '-' + me.options.theme + '-' );
				$me.addClass( '-' + value_ + '-' )
			break;

			case 'enabled':
				value_ === true ? $me.removeClass( '-disabled-' ) : $me.addClass( '-disabled-' );
			break;


		}

		me.options[ key_ ] = value_;
	}

	Notify.prototype.notify = function( message_, options_ ) {
		var me  = this
		,	$me = me.$element
		,	notification = {}
		,	header;
		
		notification.$element = $( me.options.templateNotify );
		notification._id = me._id++;
		notification.$element.attr('id', 'notification' + notification._id );
		notification.$element.css('z-index', 10*notification._id );

		options_ && $.each( options_, function( key_, value_ ) {
			switch( key_ ) {
				case 'header':
					notification.header = $( me.options.templateNotifyHeader ).append( value_ );
				break;

				case 'theme':
					notification.theme && notification.$element.removeClass( notification.theme );
					notification.$element.addClass( '-' + value_ + '-' );
					notification.theme = value_;
				break;

				case 'type':
					notification.type = value_;
				break;
			}
		});

		if (notification.type === undefined) notification.type = 4000;
		notification.content = $( me.options.templateNotifyContent ).append( message_ );

		notification.header !== undefined && typeof notification.type !== 'number' && notification.header.append('<i class="-closer">&times;</i>');
		
		notification.$element.append( notification.header !== undefined && notification.header, notification.content );

		me.notifyShow( notification );
	}

	Notify.prototype.notifyShow = function( notification_ ) {
		var me  = this
		,	$me = me.$element;

		notification_.$element.on( 'click', function( event_ ) {
			$(event_.target).hasClass('-closer') && me.notifyClose( notification_ );
		});

		me.notifications.push( notification_ );
		$me.append( notification_.$element );
		setTimeout(function(){ notification_.$element.addClass('-mx-start'); },1);

		me.notifications.length > 1 && me.closer.fadeIn();
		
		if( typeof notification_.type === 'number' ) {
			me.notifyTimerClose( notification_ );
			
			notification_.$element.on( 'mouseenter', function( event_ ) {
				clearTimeout( notification_.timer );
			});
			
			notification_.$element.on( 'mouseleave', function( event_ ) {
				me.notifyTimerClose( notification_ );
			});
		}
	}

	Notify.prototype.notifyTimerClose = function( notification_ ) {
		var me  = this
		,	$me = me.$element;

		notification_.timer = setTimeout(function(){
			me.notifyClose( notification_ );
		}, notification_.type);
	}

	Notify.prototype.notifyClose = function( notification_ ) {
		var me  = this
		,	$me = me.$element;

		$me.find('#notification'+notification_._id).animate({opacity:0}, function(){
			$(this).slideUp( function(){
				$(this).remove()
			} )
		});

		me.removeNotifyInstance( notification_ ) && me.notifications.length <= 1 && me.closer.fadeOut();
	}

	Notify.prototype.removeNotifyInstance = function( notification_ ) {
		var me  = this
		,	$me = me.$element
		,	i = 0;

		while( me.notifications[i]._id !== notification_._id && i < me.notifications.length ) i++;

		if( me.notifications[i]._id === notification_._id ) {
			me.archive.push( me.notifications[i] );
			me.notifications.splice( i, 1 );
		}

		return true;
	}

	$[_name] = function( message_, options_ ) {
		
			if( ! $('body').data( 'kit-' + _name ) ) {
				$('body').data( 'kit-' + _name, new Notify( message_, options_ ) );
			}
			else {
				options_ === undefined && typeof message_ === 'string' ? $('body').data( 'kit-' + _name).init( message_ ) : $('body').data( 'kit-' + _name).notify( message_, options_ );
			}
	}

})( jQuery, window, document );
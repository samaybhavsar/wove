/**
 * Dark-mode toggle. The no-flash inline script in <head> may already have set
 * data-theme from a saved choice; this wires the header button to flip + persist
 * it, and keeps aria-pressed in sync. With no saved choice the CSS follows the OS.
 */
( function () {
	var KEY  = 'wove-theme';
	var root = document.documentElement;
	var btn  = document.querySelector( '.wove-theme-toggle' );

	if ( ! btn ) {
		return;
	}

	function currentTheme() {
		var attr = root.getAttribute( 'data-theme' );
		if ( attr === 'dark' || attr === 'light' ) {
			return attr;
		}
		return window.matchMedia( '(prefers-color-scheme: dark)' ).matches ? 'dark' : 'light';
	}

	function sync() {
		btn.setAttribute( 'aria-pressed', String( currentTheme() === 'dark' ) );
	}

	sync();

	btn.addEventListener( 'click', function () {
		var next = currentTheme() === 'dark' ? 'light' : 'dark';
		root.setAttribute( 'data-theme', next );
		try {
			localStorage.setItem( KEY, next );
		} catch ( e ) {}
		sync();
	} );
}() );

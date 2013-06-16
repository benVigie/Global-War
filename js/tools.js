/**
*	Display message on the window. Each message has a type 'primary', 'error' or 'warning'
*	and they are display for 9 seconds.
*
*	@param: {String} str => The message to display
*	@param: {String} header => Message's header
*	@param: {String} type => Type of the message. Can be normal ('primary'), 'error' or 'warning'
*/
function displayMessage(str, header, type, time) {

	// Si aucun type specifie
	if (!type)
		type = 'primary';
	if (!time)
		time = 5000;

	$.notify(str, {
		header: header,
		theme: type,
		type: time
	});
}

function notifyError (jqXHR, textStatus) {
	console.log('Raison invoquee: [' + textStatus + ']');
	displayMessage(textStatus, 'Ouuuups !', 'error');
}

function validUpdate(data) {
	var email = document.querySelector('.user-email').innerHTML;

	if (data.error || !data.modif) {
		displayMessage(data.error, 'Ouuups !', 'error');
		return;
	}

	if (data.modif === 'notif') {
		if (data.value === '0')
			displayMessage('Vous ne recevrez plus de notification de jeu sur ' + email, 'Préférence notification');
		else
			displayMessage('Notification activées sur ' + email, 'Préférence notification');
		document.querySelector('.user-notif').setAttribute('data-notif', data.value);
	}
	if (data.modif === 'availability') {
		if (data.value === '0')
			displayMessage('Les autres joueurs ne peuvent plus vous inviter dans de nouvelles parties.', 'Joueur indisponible', 'warning');
		else
			displayMessage('Les autres joueurs vous voient et peuvent vous inviter pour de nouvelles parties !', 'Joueur disponible');
		document.querySelector('.user-availability').setAttribute('data-availability', data.value);
	}
	else if (data.modif === 'email')
		displayMessage('Vos notifications seront maintenant envoyées sur ' + data.value, "C'est noté !");
}

function toggleNotif() {
	var notif = document.querySelector('.user-notif');

	$.ajax({
		url: 'ajax/updateProfil.php?notification=' + ((notif.getAttribute('data-notif') === '0') ? '1' : '0'),
		dataType: 'json',
		success: validUpdate,
		error: notifyError
	});
}

function toggleAvailability() {
	var notif = document.querySelector('.user-availability');

	$.ajax({
		url: 'ajax/updateProfil.php?availability=' + ((notif.getAttribute('data-availability') === '0') ? '1' : '0'),
		dataType: 'json',
		success: validUpdate,
		error: notifyError
	});
}

function updateEmail() {
	var email = document.querySelector('.user-email').innerHTML;

	$.ajax({
		url: 'ajax/updateProfil.php?email=' + email,
		dataType: 'json',
		success: validUpdate,
		error: notifyError
	});
}
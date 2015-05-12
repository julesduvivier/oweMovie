// The root URL for the RESTful services
var rootURL = "http://localhost/media/api/medias";

var currentMedia;
var token;
var id;
var storeId;
var name;
var zipCode = "";




$('#louerForm').hide();
$('#infoCompte').hide();
$('#info').hide();







if (token==null)
	{
		$('#contentForm').hide();
		$('#inscForm').hide();
	}
	else
		{

			$('#authForm').hide();
		}

		$('#btnLogin').click(function() {
			login();
			return false;
		});

		$('#btnLoginClient').click(function() {
			loginClient();
			return false;
		});

		$('#btnLocationEmp').click(function() {
			var div = document.getElementById('info');
				div.style.display = 'block';
			getLocationEmp(storeId);
			return false;
		});

		$('#btnShowInscriptionClient').click(function() {
			$('#authForm').hide();
			$('#inscForm').show();
			return false;
		});
		$('#btnInscriptionCLient').click(function() {
			inscriptionClient();
			return false;
		});

		$('#btnCompte').click(function() {

			var div = document.getElementById('infoCompte');
			if (div.style.display !== 'none') {
				div.style.display = 'none';
			}
			else {
				div.style.display = 'block';
			}

			getLocation(id);
		});





		// Nothing to delete in initial application state
		$('#btnDelete').hide();

		// Register listeners
		$('#btnSearch').click(function() {
			search($('#searchKey').val());
			return false;
		});



		$('#btnLouer').click(function() {
			$('#louerForm').show();
			return false;
		});


		$('#btnLouerV').click(function() {

			btn = $('input:radio[name=sup]:checked').val();
			error = false;

			if (btn==1)
				{
					if (document.getElementById('ssd').innerHTML <= 0)
						{
							error = true;
						}
					}

					if (btn==2)
						{
							if (document.getElementById('ssc').innerHTML <= 0)
								{
									error = true;
								}
							}

							if (btn==3)
								{
									if (document.getElementById('ssb').innerHTML <= 0)
										{
											error = true;
										}
									}

									if (!error)
										{
											rentMovie();
											getLocation(id);
											$('#infoCompte').show();

										}
										else
											{
												alert("Ce film n'est plus disponible sous ce support");
											}


										});


										// Trigger search when pressing 'Return' on search key input field
										$('#searchKey').keypress(function(e){
											if(e.which == 13) {
												search($('#searchKey').val());
												e.preventDefault();
												return false;
											}
										});

										$('#btnAdd').click(function() {
											newMedia();
											return false;
										});

										$('#btnSave').click(function() {
											if ($('#mediaId').val() == '')
												{
													addMedia(storeId);
												}
												else
													{
														updateMedia(storeId);
													}
													return false;
												});

												$('#btnDelete').click(function() {
													deleteMedia();
													return false;
												});







												$('#mediaList').on('click','a', function() {
													findById($(this).data('identity'));
												});

												$('#storeList').on('click','a', function() {
													storeId = $(this).data('identity');
													console.log('storeId : ' + storeId);
													findAll($(this).data('identity'));
												});

												// Replace broken images with generic media bottle
												$("img").error(function(){
													$(this).attr("src", "img/django.jpg");

												});

												function locationOk(locationid)
												{
													console.log("Supprimer la location d'ID: "+locationid);
													$.ajax({
														type: 'DELETE',
														url: rootURL + '/location/' + locationid,
														dataType: "json",
														success: getLocationEmp(storeId)
													});
												}

												function verifToken()
												{
													if (zipCode == "")
														{
															console.log('verifToken');
															$.ajax({
																type: 'POST',
																contentType: 'application/json',
																url: rootURL + '/token',
																dataType: "json",
																data: tokenToJSON(),
																success: function(data, textStatus, jqXHR){
																	console.log(data.success.text);
																	if (data.success.text == "ok")
																		{
																			console.log("Le token à était verifié");
																		}
																		else
																			{
																				console.log("Le token est incorrect");
																				window.location.reload();
																			}
																		},
																		error: function(jqXHR, textStatus, errorThrown){
																			console.log('token error: ' + textStatus + errorThrown);
																		}
																	});
																}
															}

															function listStore()
															{
																$('.leftArea').hide();
																$('.mainArea').hide();
																$('.rightArea').hide();
																console.log('list Store');
																$.ajax({
																	type: 'GET',
																	url: rootURL + '/listStore/',
																	dataType: "json", // data type of response
																	success: renderStore
																});
															}

															function getLocation($id)
															{
																console.log('GetLocation : ' + id);
																$.ajax({
																	type: 'GET',
																	url: rootURL + '/location/' + id,
																	dataType: "json",
																	success: renderLocation
																});
															}
															function getLocationEmp($storeId)
															{
																console.log('GetLocationEmp : ' + storeId);
																$.ajax({
																	type: 'GET',
																	url: rootURL + '/locationEmp/' + storeId,
																	dataType: "json",
																	success: renderLocationEmp
																});

															}



															function login() {
																console.log('login');
																$.ajax({
																	type: 'POST',
																	contentType: 'application/json',
																	url: rootURL + '/authentification',
																	dataType: "json",
																	data: authToJSON(),
																	success: function(data, bool,textStatus, jqXHR){
																		console.log(data.success.text);
																		console.log('AUTH OK');
																		if(data.success.text != "incorrect")
																			{
																				token = data.success.text;
																				id = data.success.id;
																				storeId = data.success.storeId;
																				name = data.success.name
																				store = data.success.store
																				console.log('token : ' + token + ' id : ' + id + ' storeId : ' + storeId + ' name : ' + name + 'store : ' + store );
																				$('#contentForm').show();
																				$('#authForm').hide();
																				document.getElementById('wel').innerHTML = 'Bienvenue ' + name + '!<br/> Magasin : ' + store;
																				findAll(storeId);
																			}


																		},
																		error: function(jqXHR, textStatus, errorThrown,data){
																			console.log(data);
																			console.log('AUTH error: ' + textStatus + errorThrown);
																		}
																	});
																}


																function loginClient() {
																	console.log('loginClient');
																	$.ajax({
																		type: 'POST',
																		contentType: 'application/json',
																		url: rootURL + '/authentificationClient',
																		dataType: "json",
																		data: authToJSON(),
																		success: function(data, bool,textStatus, jqXHR){
																			console.log(data.success.text);
																			console.log('AUTH OK');
																			if(data.success.text != "incorrect")
																				{

																					id = data.success.id;
																					name = data.success.name
																					zipCode = data.success.zipCode
																					console.log('id : ' + id  + ' name : ' + name + 'zipCode : ' + zipCode );
																					$('#contentForm').show();
																					$('#authForm').hide();
																					document.getElementById('wel').innerHTML = '<h1> Bienvenue ' + name + '!</h1><br/>';
																					listStore();
																				}


																			},
																			error: function(jqXHR, textStatus, errorThrown,data){
																				alert( jqXHR.responseText);
																				console.log(data);
																				console.log('AUTH error: ' + textStatus + errorThrown);
																			}
																		});
																	}
																	function inscriptionClient() {

																		console.log('inscriptionClient');
																		$.ajax({
																			type: 'POST',
																			contentType: 'application/json',
																			url: rootURL + '/inscriptionClient',
																			dataType: "json",
																			data: inscToJSON(),
																			success: function(data, textStatus, jqXHR){
																				console.log(data.success.text);
																				console.log('inscription created successfully');
																			},
																			error: function(data, jqXHR, textStatus, errorThrown){
																				console.log('inscription error: ' + textStatus + errorThrown);
																			}
																		});
																		$('#inscForm').hide();
																		$('#authForm').show();
																	}


																	function search(searchKey) {
																		if (searchKey == '')
																			{
																				verifToken();
																				findAll(storeId);
																			}
																			else
																				{
																					verifToken();
																					findByName(searchKey);
																				}
																			}

																			function newMedia() {
																				verifToken();
																				$('#btnDelete').hide();
																				currentMedia = {};
																				renderDetails(currentMedia); // Display empty form
																			}

																			function findAll(storeId) {
																				$('#listStore').hide();
																				$('.leftArea').show();
																				$('.rightArea').show();
																				verifToken();
																				console.log('findAll' + storeId);
																				$.ajax({
																					type: 'GET',
																					url: rootURL + '/store/' + storeId,
																					dataType: "json", // data type of response
																					success: renderList
																				});
																			}



																			function findByName(searchKey) {

																				verifToken();
																				console.log('findByName: ' + searchKey);
																				$.ajax({
																					type: 'GET',
																					url: rootURL + '/search/' + searchKey,
																					dataType: "json",
																					success: renderList
																				});
																			}

																			function findById(id) {
																				$('#louerForm').hide();
																				if (zipCode!="")
																					{
																						$('.mainArea').show();
																					}
																					verifToken();
																					console.log('findById: ' + id);
																					$.ajax({
																						type: 'GET',
																						url: rootURL + '/' + id,
																						dataType: "json",
																						success: function(data){
																							$('#btnDelete').show();
																							console.log('findById success: ' + data.title);
																							currentMedia = data;
																							renderDetails(currentMedia);
																						},
																						error: function(jqXHR, textStatus, errorThrown){
																							console.log('findById error: ' + textStatus + errorThrown);
																						}
																					});
																				}

																				function infoRetard(id)
																				{
																					console.log('infoRetard : ' + id);
																					$.ajax({
																						type: 'GET',
																						url: rootURL + '/retard/' + id,
																						dataType: "json",
																						success: renderRetard
																					});
																				}

																				function addMedia(storeId) {
																					verifToken();
																					console.log('addMedia');
																					$.ajax({
																						type: 'POST',
																						contentType: 'application/json',
																						url: rootURL + '/store/' + storeId,
																						dataType: "json",
																						data: formToJSON(),
																						success: function(data, textStatus, jqXHR){
																							console.log(data);
																							console.log('media created successfully');
																							$('#btnDelete').show();
																						},
																						error: function(jqXHR, textStatus, errorThrown){
																							console.log('addMedia error: ' + textStatus + errorThrown);
																						}
																					});
																				}

																				function rentMovie()
																				{
																					console.log('rentMovie');
																					$.ajax({
																						type: 'POST',
																						contentType: 'application/json',
																						url: rootURL + '/rent/',
																						dataType: "json",
																						data: rentToJSON(),
																						success: function(data, textStatus, jqXHR){
																							console.log(data);
																							console.log('rent movie ok');
																						},
																						error: function(jqXHR, textStatus, errorThrown){
																							console.log('rent movie error: ' + textStatus + errorThrown);
																						}
																					});
																				}

																				function updateMedia(storeId) {
																					verifToken();
																					console.log('updateMedia');
																					$.ajax({
																						type: 'PUT',
																						contentType: 'application/json',
																						url: rootURL + '/store/' + storeId + '/' + $('#mediaId').val(),
																						data: formToJSON(),
																						success: function(data, textStatus, jqXHR){
																							console.log(data);
																							console.log('media updated successfully');
																						},
																						error: function(jqXHR, textStatus, errorThrown){
																							console.log('media update error: ' + textStatus );
																						}
																					});
																				}

																				function backMedia(locationId)
																				{
																					var div = document.getElementById('info');

																					div.style.display = 'none';
																					verifToken();
																					console.log('backMedia');
																					$.ajax({
																						type: 'PUT',
																						contentType: 'application/json',
																						url: rootURL + '/back/' + locationId,
																						success: function(data, textStatus, jqXHR){
																							console.log(data);
																							console.log('media back successfully');
																						},
																						error: function(jqXHR, textStatus, errorThrown){
																							console.log('media back error: ' + textStatus );
																						}
																					});

																						getLocationEmp(storeId);
																						div.style.display ='block';


																				}

																				function deleteMedia() {
																					verifToken();
																					console.log('deleteMedia');
																					$.ajax({
																						type: 'DELETE',
																						url: rootURL + '/' + $('#mediaId').val(),
																						success: function(data, textStatus, jqXHR){
																							console.log(data);
																							console.log('media deleted successfully');
																						},
																						error: function(jqXHR, textStatus, errorThrown){
																							console.log('deleteMedia error');
																						}
																					});
																				}

																				function renderList(data) {
																					verifToken();
																					// JAX-RS serializes an empty list as null, and a 'collection of one' as an object (not an 'array of one')
																					console.log(data);

																					var list = data == null ? [] : (data.medias instanceof Array ? data.medias : [data.medias]);
																					console.log(list);
																					$('#mediaList li').remove();
																					$.each(list, function(index, medias) {
																						$('#mediaList').append('<li><a href="#" data-identity="' + medias.id + '">'+medias.title+'</a></li>');
																					});
																				}

																				function renderStore(data)
																				{

																					console.log(data.store);

																					var list = data == null ? [] : (data.store instanceof Array ? data.store : [data.store]);
																					console.log(list);
																					console.log('a');
																					$('#storeList li').remove();
																					$.each(list, function(index, store) {
																						$('#storeList').append('<li><a href="#" data-identity="' + store.id + '"><h3>'+store.name+'</h3></a></li>');
																					});
																				}

																				function renderLocation(data)
																				{
																					console.log(data.location);

																					var list = data == null ? [] : (data.location instanceof Array ? data.location : [data.location]);
																					console.log(list);
																					console.log('a');
																					document.getElementById("locationList").innerHTML=" <thead><tr><th>Titre</th><th>Support</th><th>Magasin</th><th>Date Location</th><th>Date d'expiration</th><th>Date de retour</th></tr>    </thead>";
																					$.each(list, function(index, location) {
																						console.log(location);
																						document.getElementById("locationList").innerHTML+='<tr><td>'+location.title+'</h3></td><td>' + location.support_id +'</td><td>' + location.name +'</td><td>' + location.date +'</td><td>' + location.expirationdate +'</td><td>' + location.backdate +'</td></tr>';

																					});



																				}

																				function renderRetard(data)
																				{
																					console.log(data.retard[0]);
																					document.getElementsByClassName('modal-title')[0].innerHTML="<h3>Information " + data.retard[0].firstname + "</h3>";
																					document.getElementsByClassName('modal-body')[0].innerHTML= data.retard[0].firstname + ' ' + data.retard[0].lastname + ' aurait du rendre le film ' + data.retard[0].title + ' il y a ' +  retardSave + ' jours.';
																					document.getElementsByClassName('modal-body')[1].innerHTML= "<small>Contact par téléphone : <b>" + data.retard[0].phone  + "</b><br/>Contact par email : <b>" + data.retard[0].email + "</b></small>";
																					$(document).ready(function(){
																						jQuery("#myModal").modal('show');
																					});
																				}



																				function renderLocationEmp(data)
																				{
																					console.log(data.location);

																					var list = data == null ? [] : (data.location instanceof Array ? data.location : [data.location]);
																					console.log(list);
																					console.log('a');

																					var today = new Date();





																					document.getElementById("locationList").innerHTML=" <thead><tr><th>Titre</th><th>Support</th><th>Magasin</th><th>Date Location</th><th>Date d'expiration</th><th>ID location</th><th>Retour</th><th>Retard</th></tr>    </thead>";
																					$.each(list, function(index, location) {
																						var retour = new Date(location.expirationdate);
																						console.log('retour : ' + retour);
																						 ecart = (dateDiff(today,retour).day);

																						if (location.backdate == "/")
																							{
																								backdate = '<p  text-align:center;><button type="button"   data-identity="' + location.locationid + '" class="retourFilm btn btn-default glyphicon glyphicon-ok "></button></p>';
																							}
																							else
																								{
																									backdate = "<i>Location terminée</i>";
																								}

																						if (ecart>0)
																							retard = "<p style='color:green; text-align:center;'>Il reste " + ecart + " jours</p>";
																							else
																								retard = "<p style='color:red; text-align:center;'>Retard de " + -ecart + " jours<br/><button type='button' data-id='" + -ecart + "' data-identity='" + location.locationid + "' class='relancer btn btn-default glyphicon glyphicon-eye-open'></button></p>";
																								document.getElementById("locationList").innerHTML+='<tr><td>'+location.title+'</h3></td><td>' + location.support_id +'</td><td>' + location.name +'</td><td>' + location.date +'</td><td>' + location.expirationdate +'</td><td>' + location.locationid +'</td><td>' + backdate + '</td><td> ' + retard + '</td></tr>';
																							});

																							$('.relancer').click(function() {
																								console.log('retard' + $(this).data('identity'));
																								infoRetard($(this).data('identity'));
																								retardSave = $(this).data('id');
																							});

																							$('.retourFilm').click(function() {
																								console.log('retour film' +  $(this).data('identity'));
																								backMedia(($(this).data('identity')));
																							});

																						}




																						function dateDiff(date1, date2){
																							var diff = {}                           // Initialisation du retour
																							var tmp = date2 - date1;

																							tmp = Math.floor(tmp/1000);             // Nombre de secondes entre les 2 dates
																							diff.sec = tmp % 60;                    // Extraction du nombre de secondes

																							tmp = Math.floor((tmp-diff.sec)/60);    // Nombre de minutes (partie entière)
																							diff.min = tmp % 60;                    // Extraction du nombre de minutes

																							tmp = Math.floor((tmp-diff.min)/60);    // Nombre d'heures (entières)
																							diff.hour = tmp % 24;                   // Extraction du nombre d'heures

																							tmp = Math.floor((tmp-diff.hour)/24);   // Nombre de jours restants
																							diff.day = tmp;

																							return diff;
																						}

																						function renderDetails(media) {
																							verifToken();
																							if (zipCode == "")
																								{
																									$('#mediaId').val(media.id);
																									$('#title').val(media.title);
																									$('#genre').val(media.genre);
																									$('#acteur').val(media.actor);
																									$('#realisateur').val(media.realisator);
																									$('#producteur').val(media.productor);
																									$('#synopsis').val(media.synopsis);
																									$('#releaseDate').val(media.publication_date);
																									$('#rate').val(media.rate);
																									$('#stockDvd').val(media.stockDvd);
																									$('#stockCassette').val(media.stockCassette);
																									$('#stockBluray').val(media.stockBluray);
																									$('#pic').attr('src', 'img/' + media.illustration);
																								}
																								else
																									{
																										$('#mid').val(media.id);
																										document.getElementById('mtitle').innerHTML=media.title;
																										document.getElementById('rentTitle').innerHTML="Louer " + media.title;
																										document.getElementById('mgenre').innerHTML= "Genre : " + media.genre;
																										document.getElementById('macteurs').innerHTML= "Acteurs : " + media.actor;
																										document.getElementById('mrealisateurs').innerHTML= "Realisateurs : " + media.realisator;
																										document.getElementById('mproducteurs').innerHTML= "Prodcteurs : " + media.productor;
																										document.getElementById('mnote').innerHTML= "Note du public: " + media.rate;
																										document.getElementById('mds').innerHTML= "Date de sortie : " + media.publication_date	;
																										document.getElementById('msd').innerHTML= "Stock DVD : <div id='ssd'>" + media.stockDvd + "</div>";
																										document.getElementById('msc').innerHTML= "Stock Cassette : <div id='ssc'>" + media.stockCassette + "</div>";
																										document.getElementById('msb').innerHTML= "Stock stockBluray :<div id='ssb'> " + media.stockBluray + "</div>";
																										document.getElementById('mdesc').innerHTML= "Synopsis : " + media.synopsis;
																										$('#pic').attr('src', 'img/' + media.illustration);

																										if(media.stockDvd <=0)
																											$('input[id=sup1]').attr("disabled",true);
																											else
																												$('input[id=sup1]').attr("disabled",false);

																												if(media.stockCassette <=0)
																													$('input[id=sup2]').attr("disabled",true);
																													else
																														$('input[id=sup2]').attr("disabled",false);

																														if(media.stockBluray <=0)
																															$('input[id=sup3]').attr("disabled",true);
																															else
																																$('input[id=sup3]').attr("disabled",false);
																															}
																														}

																														// Helper function to serialize all the form fields into a JSON string
																														function formToJSON() {


																															var z =  JSON.stringify({
																																"id": $('#mediaId').val(),
																																"titre": $('#title').val(),
																																"genre": $('#genre').val(),
																																"actor": $('#acteur').val(),
																																"realisator": $('#realisateur').val(),
																																"productor": $('#producteur').val(),
																																"synopsis": $('#synopsis').val(),
																																"publication_date": $('#releaseDate').val(),
																																"rate": $('#rate').val(),
																																"stockDvd": $('#stockDvd').val(),
																																"stockCassette": $('#stockCassette').val(),
																																"stockBluray": $('#stockBluray').val()
																															});
																															console.log(z);
																															return z;
																														}

																														function rentToJSON() {


																															var z =  JSON.stringify({
																																"id": $('#mid').val(),
																																"duree": $('input:radio[name=duree]:checked').val(),
																																"genre": $('input:radio[name=sup]:checked').val(),
																																"sd" : document.getElementById('ssd').innerHTML,
																																"sc" : document.getElementById('ssc').innerHTML,
																																"sb" : document.getElementById('ssb').innerHTML,
																																"storeId":storeId,
																																"idClient":id
																															});
																															console.log(z);
																															return z;
																														}

																														function authToJSON() {

																															var z =  JSON.stringify({
																																"email": $('#email').val(),
																																"password": $('#password').val()
																															});
																															console.log(z);
																															return z;
																														}
																														function inscToJSON() {

																															var z =  JSON.stringify({
																																"email": $('#emailI').val(),
																																"password": $('#passwordI').val(),
																																"firstname":$('#firstname').val(),
																																"lastname":$('#lastname').val(),
																																"birthdate":$('#birthdate').val(),
																																"phone":$('#phone').val(),
																																"address":$('#address').val(),
																																"zipcode":$('#zipcode').val()
																															});
																															console.log(z);
																															return z;
																														}

																														function tokenToJSON() {

																															var z =  JSON.stringify({
																																"id": id,
																																"token": token
																															});
																															console.log(z);
																															return z;
																														}

																														function whoToJSON() {

																															var z =  JSON.stringify({
																																"id": id,
																																"storeId": storeId
																															});
																															console.log(z);
																															return z;
																														}

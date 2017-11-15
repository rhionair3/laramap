<!DOCTYPE html>
<html>
  <head>
    <title>LARAMAPS</title>
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"/>
    <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        width: 300px;
        height: 300px;
        display: none;
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      .controls {
        margin-top: 10px;
        border: 1px solid transparent;
        border-radius: 2px 0 0 2px;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        height: 32px;
        outline: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
      }

      #mode-selector {
        color: #fff;
        background-color: #4d90fe;
        margin-left: 12px;
        padding: 5px 11px 0px 11px;
      }

      #mode-selector label {
        font-family: Roboto;
        font-size: 13px;
        font-weight: 300;
      }

    </style>
  </head>
  <body>
    <div class="row">
        <div class="container">
            <div class="col-lg-3">
                <div class="form-group">
                    <label>Tempat Asal</label>
                    <input type="text" name="tempatAsal" class="form-control" id="tempatAsal" placeholder="Pilih Tempat Asal" />
                </div>
                <div class="form-group">
                    <label>Tempat Tujuan</label>
                    <input type="text" name="tempatTujuan" class="form-control" id="tempatTujuan" placeholder="Pilih Tempat Tujuan" />
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <label>Tempat Asal</label>
                    <div class="input-group">
                        <div class="input-group-addon">Lat</div>
                        <input type="text" class="form-control" id="oriLat">
                        <div class="input-group-addon">Long</div>
                        <input type="text" class="form-control" id="oriLang">
                    </div>
                </div>
                <div class="form-group">
                    <label>Tempat Tujuan</label>
                    <div class="input-group">
                        <div class="input-group-addon">Lat</div>
                        <input type="text" class="form-control" id="desLat">
                        <div class="input-group-addon">Long</div>
                        <input type="text" class="form-control" id="desLang">
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group" name="jenisKendaraan">
                    <label>Kendaraan Yang Digunakan</label>
                    <select class="form-control" id="jenisKendaraan">
                        <option value="MOTOR"> MOTOR</option>
                        <option value="MOBIL"> MOBIL</option>
                        <option value="KENDARAAN_UMUM"> KENDARAAN UMUM</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>&nbsp;&nbsp;</label>
                    <button class="btn btn-primary btn-block" id="submit" name="submit"> KALKULASI BIAYA </button>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="container">
            <div class="col-lg-6">
                <div class="form-group">
                    <label>JSON Request</label>
                    <textarea class="form-control" rows="20" id="requestData" placeholder="Otomatis Terisi Jika Klik 'KALKULASI BIAYA'"></textarea>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <label>JSON Response</label>
                    <textarea class="form-control" id="responseData" rows="20" placeholder="Otomatis Terisi Jika Klik 'KALKULASI BIAYA'"></textarea>
                </div>
            </div>
        </div>
    </div>
    <div id="map"></div>
    <script src="https://code.jquery.com/jquery-1.12.4.js" integrity="sha256-Qw82+bXyGq6MydymqBxNPYTaUXXq7c8v3CwiYwLLNXU=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script>
        function initMap() {
            var map = new google.maps.Map(document.getElementById('map'), {
              mapTypeControl: false,
              center: {lat: -33.8688, lng: 151.2195},
              zoom: 13
            });

            new AutocompleteDirectionsHandler(map);
        }

        function AutocompleteDirectionsHandler(map) {
            this.map = map;
            this.originPlaceId = null;
            this.destinationPlaceId = null;
            this.travelMode = 'WALKING';
            var originInput = document.getElementById('tempatAsal');
            var destinationInput = document.getElementById('tempatTujuan');
            var modeSelector = document.getElementById('mode-selector');
            this.directionsService = new google.maps.DirectionsService;
            this.directionsDisplay = new google.maps.DirectionsRenderer;
            this.directionsDisplay.setMap(map);

            var originAutocomplete = new google.maps.places.Autocomplete(originInput);
            var destinationAutocomplete = new google.maps.places.Autocomplete(destinationInput);

            // this.setupClickListener('changemode-walking', 'WALKING');
            // this.setupClickListener('changemode-transit', 'TRANSIT');
            // this.setupClickListener('changemode-driving', 'DRIVING');

            this.setupPlaceChangedListener(originAutocomplete, 'ORIG');
            this.setupPlaceChangedListener(destinationAutocomplete, 'DEST');
            google.maps.event.addListener(originAutocomplete, 'place_changed', function () {
                var place1 = originAutocomplete.getPlace();
                document.getElementById('oriLat').value = place1.geometry.location.lat();
                document.getElementById('oriLang').value = place1.geometry.location.lng();
            });
            google.maps.event.addListener(destinationAutocomplete, 'place_changed', function () {
                var place2 = destinationAutocomplete.getPlace();
                document.getElementById('desLat').value = place2.geometry.location.lat();
                document.getElementById('desLang').value = place2.geometry.location.lng();
            });

        }

        AutocompleteDirectionsHandler.prototype.setupPlaceChangedListener = function(autocomplete, mode) {
            var me = this;
            autocomplete.bindTo('bounds', this.map);
            autocomplete.addListener('place_changed', function() {
              var place = autocomplete.getPlace();
              if (!place.place_id) {
                window.alert("Please select an option from the dropdown list.");
                return;
              }
              if (mode === 'ORIG') {
                me.originPlaceId = place.place_id;
              } else {
                me.destinationPlaceId = place.place_id;
              }
              me.route();
            });

        };

        AutocompleteDirectionsHandler.prototype.route = function() {
        if (!this.originPlaceId || !this.destinationPlaceId) {
          return;
        }
        var me = this;

        this.directionsService.route({
          origin: {'placeId': this.originPlaceId},
          destination: {'placeId': this.destinationPlaceId},
          travelMode: this.travelMode
        }, function(response, status) {
          if (status === 'OK') {
            me.directionsDisplay.setDirections(response);
          } else {
            window.alert('Directions request failed due to ' + status);
          }
        });
        };

        $('#submit').click(function(){
            var crToken = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                method: 'POST',
                url: 'calc',
                data: { _token          : crToken,
                        oriLat          : $('#oriLat').val(),
                        oriLang         : $('#oriLang').val(),
                        desLat          : $('#desLat').val(),
                        desLang         : $('#desLang').val(),
                        jenisKendaraan  : $('#jenisKendaraan').val(),
                      },
                success: function(data) {
                    obj = JSON.parse(data);
                    document.getElementById('requestData').innerHTML = JSON.stringify(obj.requests,undefined, 2);
                    document.getElementById('responseData').innerHTML = JSON.stringify(obj.response,undefined, 2);
                    console.log(data);
                }
            });

            return false;
        });

    </script>

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyABGtkCVK6skuMMVrkApS5CAp1K_yImnUc&libraries=places&callback=initMap"
        async defer></script>
  </body>
</html>
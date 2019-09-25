/*
 * Copyright 2017 Google Inc. All rights reserved.
 *
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

  // Escapes HTML characters in the infoWindow template, to avoid XSS.
  function escapeHTML(strings) {
    let result = strings[0];
    for (let i = 1; i < arguments.length; i++) {
      result += String(arguments[i]).replace(/[&<>'"]/g, (c) => {
        return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c];
      });
      result += strings[i];
    };
    return result;
  }
  
  function initMap() {
  
    // Create the map.
    const map = new google.maps.Map(document.getElementsByClassName('map')[0], {
      zoom: 10,
      center: {lat: latitude, lng: longitude}
    });
  
    // Load the stores GeoJSON onto the map.
    map.data.loadGeoJson('includes/views/scripts/project.php');
  
    // Define the custom marker icons, using the store's "category".
    map.data.setStyle(
      
      feature => {
      return {
        icon: {
          url: `img/`+feature.getProperty('marker')+`.png`,
          scaledSize: new google.maps.Size(24, 48)
        }
      };
    });
  
    const infoWindow = new google.maps.InfoWindow();
    infoWindow.setOptions({pixelOffset: new google.maps.Size(0, -30)});
  
    // Show the information for a store when its marker is clicked.
    map.data.addListener('click', event => {
  
      const url = event.feature.getProperty('url');
      const cover = event.feature.getProperty('cover');
      const name = event.feature.getProperty('name');
      const description = event.feature.getProperty('description');
      const fee = event.feature.getProperty('fee');
      const address = event.feature.getProperty('address');
      const position = event.feature.getGeometry().get();
      const content = escapeHTML`
      <div id="content">
        <a href="${url}"><img style="float:left; width:200px; margin-top:30px" src="${cover}"></a>
        <div style="margin-left:220px; margin-bottom:20px;">
          <h2><a href="${url}">${name}</a></h2><p>${description}</p>
          <p><b>Amount Offered:</b> ${fee}<br/><b>Address:</b> ${address}</p>
          <p><img src="https://maps.googleapis.com/maps/api/streetview?size=350x120&location=${position.lat()},${position.lng()}&key=${apiKey}"></p>
        </div>
      </div>
        `;
  
      infoWindow.setContent(content);
      infoWindow.setPosition(position);
      infoWindow.open(map);
    });
  
  }
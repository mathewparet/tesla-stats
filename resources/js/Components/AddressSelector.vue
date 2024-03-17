<script setup>
    import { MapboxMap, MapboxGeocoder, MapboxGeolocateControl, MapboxMarker } from '@studiometa/vue-mapbox-gl';
    import 'mapbox-gl/dist/mapbox-gl.css';
    import '@mapbox/mapbox-gl-geocoder/lib/mapbox-gl-geocoder.css';
    import MapboxCircle from 'mapbox-gl-circle'
    import { ref, watch } from 'vue';

    const latitude = defineModel('latitude');
    const longitude = defineModel('longitude');
    const address = defineModel('address');
    // const radius = defineModel('radius');
    
    const props = defineProps({
        accessToken: {
            type: String,
            default: ''
        },
        zoom: {
            type: Number,
            default: 15
        },
        radius: {
            type: Number,
            default: 0,
        },
        minRadius: {
            type: Number,
            default: 0
        },
        fillColor: {
            type: String,
            default: 'blue'
        },
    })

    const map = ref(null);

    var myCircle = null;

    
    
    const updateCircle = () => {
        myCircle.setCenter({lat: props.latitude, lng: props.longitude});
        myCircle.setRadius(props.radius);
    }

    watch(() => props.radius, async(oldRadius, newRadius) => {
        updateCircle()
    })

    watch(() => props.latitude, async(oldLat, newLat) => updateCircle())
    watch(() => props.longitude, async(oldLat, newLat) => updateCircle())

    const mapLoaded = (map) => {
        myCircle = new MapboxCircle({lat:  props.latitude, lng: props.longitude}, props.radius, {
            minRadius: props.minRadius,
            fillColor: props.fillColor
        }).addTo(map);
    }

    const setLocation = (lat, lng) => {
        latitude.value = lat
        longitude.value = lng
        address.value = lat + ', ' + lng
    }
    

    const setAddress = (result) => {
        latitude.value = result.geometry.coordinates[1]
        longitude.value = result.geometry.coordinates[0]
        address.value = result.place_name
    }

    defineExpose({
        updateRadius: () => updateCircle(),
    })
</script>
<template>
    <MapboxMap 
        style="margin-top: 1em; height: 300px;"
        :access-token="accessToken"
        ref="map"
        :center="[longitude, latitude]"
        :zoom="zoom"
        @mb-created="mapLoaded"
        @mb-click="(e) => setLocation(e.lngLat.lat, e.lngLat.lng)"
        map-style="mapbox://styles/mapbox/streets-v11">
        <MapboxGeolocateControl position="top-left" @mb-geolocate="(e) => setLocation(e.coords.latitude, e.coords.longitude)"/>
        <MapboxMarker v-if="latitude && longitude" :lng-lat="[longitude, latitude]"/>
        <MapboxGeocoder type="address"  :proximity="{latitude: latitude, longitude: longitude}" class="mt-4" types="address" ref="mbox" @mb-result="(result) => setAddress(result.result) "/>
    </MapboxMap>
</template>
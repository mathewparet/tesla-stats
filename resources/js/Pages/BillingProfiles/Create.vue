<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { Link, useForm, usePage } from '@inertiajs/vue3';
    import ActionMessage from '@/Components/ActionMessage.vue';
    import FormSection from '@/Components/FormSection.vue';
    import InputError from '@/Components/InputError.vue';
    import InputHelp from '@/Components/InputHelp.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import TextInput from '@/Components/TextInput.vue';
    import SelectInput from '@/Components/SelectInput.vue';
    import { computed, ref, onMounted } from 'vue';
    import Checkbox from '@/Components/Checkbox.vue';
    import { MapboxMap, MapboxGeocoder, MapboxGeolocateControl, MapboxMarker } from '@studiometa/vue-mapbox-gl';
    import 'mapbox-gl/dist/mapbox-gl.css';
    import '@mapbox/mapbox-gl-geocoder/lib/mapbox-gl-geocoder.css';
    import MapboxCircle from 'mapbox-gl-circle'
    import RangeInput from '@/Components/RangeInput.vue';

    const props = defineProps({
        billingProfile: Object,
        vehicles: Object,
        editMode: Boolean,
        timeZones: Array,
    });

    var myCircle = null;

    const mapLoaded = (map) => {
        myCircle = new MapboxCircle({lat:  0, lng: 0}, form.radius, {
            minRadius: form.radius,
            fillColor: 'blue'
        }).addTo(map);
        
        if(form.address)
        {
            updateCircle();
        }
    }

    const currentVehicle = computed(() => (new URLSearchParams(window.location.search)).get('vehicle_id'));

    const form = useForm({
        name: props.billingProfile?.name || '',
        timezone: props.billingProfile?.timezone || Intl.DateTimeFormat().resolvedOptions().timeZone,
        bill_day: props.billingProfile?.bill_day || '',
        activated_on: props.billingProfile?.activated_on || '',
        deactivated_on: props.billingProfile?.deactivated_on || '',
        vehicles: props.billingProfile?.vehicles.map(vehicle => vehicle.id) || [currentVehicle.value],
        address: props.billingProfile?.address || '',
        latitude: props.billingProfile?.latitude || -33.86150144690208,
        longitude: props.billingProfile?.longitude || 151.21060996939127,
        radius: props.billingProfile?.radius || 67,
    })


    const vehicles = ref(null)
    const mbox = ref(null)
    const map = ref(null)

    const setCoords = (latitude, longitude) => {
        form.latitude = latitude
        form.longitude = longitude
        form.address = latitude + '°, ' + longitude +'°'
        updateCircle()
    }

    const updateCircle = () => {
        myCircle.setCenter({lat: form.latitude, lng: form.longitude});
        myCircle.setRadius(form.radius);
    }

    const setAddress = (result) => {
        setCoords(result.geometry.coordinates[1], result.geometry.coordinates[0])
        form.address = result.place_name
    }

    const createModel = () => {
        form.post(route('billing-profiles.store'), {
            preserveScroll: true,
        })
    }

    const updateModel = () => {
        form.put(route('billing-profiles.update', {billing_profile: props.billingProfile.id}), {
            preserveScroll: true,
        })
    }

    const submitForm = () => props.editMode ? updateModel() : createModel()

    const action = computed(() => props.editMode ? 'Update' : 'Create')

    const title = computed(() => props.editMode ? 'Edit Billing Profile' : 'Create Billing Profile')

</script>

<template>
    <AppLayout :title="title">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                <Link :href="route('billing-profiles.index')" class="font-medium text-gray-600 dark:text-gray-500 hover:underline">Billing Profiles</Link> <span class="font-medium text-gray-600 dark:text-gray-500" v-if="billingProfile?.id"> / <Link :href="route('billing-profiles.show', {billing_profile: billingProfile.id})" class="font-medium text-gray-600 dark:text-gray-500 hover:underline">{{ billingProfile.name }}</Link></span> / {{ action }}
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <FormSection @submitted="submitForm">
                    <template #title>
                        {{ title }}
                    </template>

                    <template #description>
                        Billing Profiles define when a bill is generated, and between which dates the service was active with that provider.
                    </template>

                    <template #form>
                        <!-- Name -->
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="name" value="Name" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                                placeholder="My Electric Company"
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="bill_day" value="Bill Day" />
                            <TextInput
                                id="bill_day"
                                v-model="form.bill_day"
                                type="number"
                                min="1"
                                max="31"
                                class="mt-1 block w-full"
                                required
                                placeholder="1-31"
                            />
                            <InputError :message="form.errors.bill_day" class="mt-2" />
                        </div>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="activated_on" value="Activated On" />
                            <TextInput
                                id="activated_on"
                                v-model="form.activated_on"
                                type="date"
                                required
                                class="mt-1 block w-full"
                                placeholder="1-31"
                            />
                            <InputError :message="form.errors.activated_on" class="mt-2" />
                        </div>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="deactivated_on" value="Deactivated On" />
                            <TextInput
                                id="deactivated_on"
                                v-model="form.deactivated_on"
                                type="date"
                                class="mt-1 block w-full"
                                placeholder="1-31"
                            />
                            <InputError :message="form.errors.deactivated_on" class="mt-2" />
                        </div>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="timezone" value="Timezone" />
                            <SelectInput
                                id="timezone"
                                :options="timeZones"
                                v-model="form.timezone"
                                class="mt-1 block w-full"
                                placeholder="1-31"
                            />
                            <InputError :message="form.errors.timezone" class="mt-2" />
                        </div>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="address" value="Service Address" />
                            <div>
                                <MapboxMap 
                                    class="mt-1 block w-full rounded"
                                    style="margin-top: 1em; height: 300px;"
                                    :access-token="$page.props.config.geocode.mapbox.key"
                                    ref="map"
                                    :center="[form.longitude || 0, form.latitude || 0]"
                                    :zoom="15"
                                    @mb-created="mapLoaded"
                                    @mb-click="(e) => setCoords(e.lngLat.lat, e.lngLat.lng)"
                                    map-style="mapbox://styles/mapbox/streets-v11">
                                    <MapboxGeolocateControl position="top-left" @mb-geolocate="(e) => setCoords(e.coords.latitude, e.coords.longitude)"/>
                                    <MapboxMarker v-if="form.address" :lng-lat="[form.longitude, form.latitude]"/>
                                    <MapboxGeocoder type="address"  :proximity="{latitude: form.latitude, longitude: form.longitude}" class="mt-4" types="address" ref="mbox" @mb-result="(result) => setAddress(result.result) "/>
                                </MapboxMap>
                                <div>{{ form.address }}</div>
                            </div>
                            <InputError :message="form.errors.address" class="mt-2" />
                        </div>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="radius" value="Radius" />
                            <RangeInput
                                min="25" 
                                max="500" 
                                v-model="form.radius"
                                @input="updateCircle"
                            />
                            <InputHelp>Radius in meters. Any cost related to charging within this radius from your address will be considered in the bill.</InputHelp>
                            <InputError :message="form.errors.radius" class="mt-2" />
                        </div>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel value="Vehicles"/>
                            <div class="flex items-center mt-1 block w-full" v-for="vehicle in props.vehicles.data" :key="vehicle">
                                <Checkbox :id="'vehicles-' + vehicle.id" v-model:checked="form.vehicles" :value="vehicle.id" name="vehicles" />

                                <div class="ms-2">
                                    <InputLabel :for="'vehicles-' + vehicle.id">
                                        {{ vehicle.name }} - {{ vehicle.plate }} / {{ vehicle.masked_vin }}
                                    </InputLabel>
                                </div>
                            </div>
                            <InputError :message="form.errors.vehicles" class="mt-2" />
                        </div>
                    </template>

                    <template #actions>
                        <ActionMessage :on="form.recentlySuccessful" class="mr-3">Saved.</ActionMessage>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            {{ action }}
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </div>
    </AppLayout>
</template>
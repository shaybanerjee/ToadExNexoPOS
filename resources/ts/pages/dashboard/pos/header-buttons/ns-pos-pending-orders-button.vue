<script>
import { Popup } from '~/libraries/popup';
import nsPosPendingOrdersPopup from '~/popups/ns-pos-pending-orders-popup.vue';
import { __ } from '~/libraries/lang';

export default {
    name: 'ns-pos-pending-orders-button',
    methods: {
        __,

        openPendingOrdersPopup() {
            Popup.show( nsPosPendingOrdersPopup );
        }
    },
}
</script>
<template>
</template>
<script>
    $(document).ready(function () {
        let priceSlider = document.getElementById('priceSlider');
        let min_input = document.getElementById('minPrice');
        let max_input = document.getElementById('maxPrice');
        let inputs = [min_input, max_input];

        let min_price = $("#minPrice").val();
        let max_price = $("#maxPrice").val();
        let get_max_price = $("#maxPrice").data('max-val');

        noUiSlider.create(priceSlider, {
            start: [min_price, max_price],
            connect: true,
            tooltips: [true, wNumb({decimals: 0})],
            range: {
                'min': 1,
                'max': parseInt(get_max_price)
            }
        });

        priceSlider.noUiSlider.on('update', function (values, handle) {
            inputs[handle].value = Math.round(values[handle]);
        });

        priceSlider.noUiSlider.on('change', function (values, handle) {
            inputs[handle].value = Math.round(values[handle]);
            let my_form_id = $('#filter-data').get(0);
            filterForm(my_form_id);
        });
    });
</script>

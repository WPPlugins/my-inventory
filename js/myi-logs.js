(function ($) {
    $(document).ready(function(){
        $('input[name="daterange"]').daterangepicker({
            timePicker: true,
            timePickerIncrement: 1,
            showDropdowns : true,
            linkedCalendars: false,
            autoUpdateInput: false,
            locale: {
                format: 'DD/MM/YYYY h:mm A',
                cancelLabel: 'Clear'
            }
        });
        
        // apply button
        $('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY h:mm A') + ' - ' + picker.endDate.format('DD/MM/YYYY h:mm A'));
        });

        // clear button
        $('input[name="daterange"]').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
       
        $('#report').DataTable( {
                dom: 'Bfrtip',
                responsive: true,
                buttons: [
                    'copy', 'excel', 'csv', 'print',
                    {
                        extend: 'pdfHtml5',
                        text: 'Pdf',
                        orientation: 'landscape',
                        pageSize: 'A4'
                    }
                ],

                order:[[1,'desc']]
            });
    })
})(jQuery);
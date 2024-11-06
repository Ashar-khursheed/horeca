<button id="showChangesButton" class="btn btn-primary">Show Product Changes</button>

<!-- Include Modal Structure Here -->
<div class="modal" id="changesModal" tabindex="-1" role="dialog" aria-labelledby="changesModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changesModalLabel">Product Changes</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="modalContent">Loading...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#showChangesButton').on('click', function() {
            $('#modalContent').html('Loading...');

            $.ajax({
                url: '{{ route("temp-products.index") }}',
                method: 'GET',
                success: function(data) {
                    $('#modalContent').html(data);
                },
                error: function() {
                    $('#modalContent').html('<p>Error loading data.</p>');
                }
            });

            $('#changesModal').modal('show');
        });
    });
</script>

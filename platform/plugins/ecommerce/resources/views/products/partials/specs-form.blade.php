<div id="specs-wrapper">
    @foreach($specs as $spec)
        <div class="spec-item">
            <input type="text" name="specs[{{ $loop->index }}][name]" value="{{ old("specs.$loop->index.name", $spec->spec_name) }}" placeholder="Spec Name" />
            <input type="text" name="specs[{{ $loop->index }}][value]" value="{{ old("specs.$loop->index.value", $spec->spec_value) }}" placeholder="Spec Value" />
            <button type="button" class="remove-spec">Remove</button>
        </div>
    @endforeach
</div>
<button type="button" id="add-spec">Add Spec</button>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('add-spec').addEventListener('click', function() {
            const wrapper = document.getElementById('specs-wrapper');
            const index = wrapper.children.length; // Get the current index
            
            // Create a new spec element
            const newSpec = document.createElement('div');
            newSpec.className = 'spec-item';
            newSpec.innerHTML = `
                <input type="text" name="specs[${index}][name]" placeholder="Spec Name" />
                <input type="text" name="specs[${index}][value]" placeholder="Spec Value" />
                <button type="button" class="remove-spec">Remove</button>
            `;

            // Append the new spec
            wrapper.appendChild(newSpec);
            console.log('New spec added'); // Debugging line
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-spec')) {
                e.target.closest('.spec-item').remove();
                console.log('Spec removed'); // Debugging line
            }
        });
    });
</script>

<!DOCTYPE html>
<html>
<head>
    <title>Import Santri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Import Santri</h2>

    <form action="{{ route('tenant.santri.import.preview') }}" 
          method="POST" 
          enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label>Upload File Excel / CSV</label>
            <input type="file" name="file" class="form-control" required>
        </div>

        <button class="btn btn-primary">Preview</button>
    </form>
</div>

</body>
</html>
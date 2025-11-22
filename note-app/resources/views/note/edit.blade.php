<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('/css/style.css')  }}">
    <link rel="stylesheet" href="{{ asset('/css/create.css')  }}" >
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/idea.min.css">
    <title>【CodeNote】編集</title>
</head>
<body>
<header>
    <h1><a href="{{ route('note') }}">CodeNote</a></h1>
</header>
<p>ここに文字が出れば Blade は表示されている</p> 
<form method="POST" action="{{ route('notes.update', $note->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <label for="title">タイトル</label>
    <input type="text" id="title" name="title" value="{{ $note->title }}">

    <label for="editor">内容 (Markdown)</label>
    <textarea id="editor" name="content">{{ $note->content }}</textarea>

    <button type="submit">保存</button>
    

    <!-- 画像アップロード -->
    <div style="margin-top:20px;">
        <input type="file" id="imageUpload" style="display:none;">
        <button type="button" id="imageUploadButton">画像アップロード</button>
    </div>

</form>

<script>
    const imageInput = document.getElementById('imageUpload');
    const uploadButton = document.getElementById('imageUploadButton');

    uploadButton.addEventListener('click', () => {
        imageInput.click();
    });

    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('image', file);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route('image.upload') }}', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.url) {
                alert('画像アップロード成功: ' + data.url);
            } else {
                alert('アップロード失敗');
            }
        })
        .catch(err => console.error(err));
    });
</script>
</body>
</html>
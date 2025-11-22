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
<form method="POST" id="noteForm" action="{{ route('notes.update', $note->id) }}">
    @csrf
    @method('PUT')
        <div class="editor-container" >
        <!-- 上段: タイトル・内容 -->
            <div class="editor-top">
                <label for="title">タイトル</label>
                <input type="text" id="title" name="title" value="{{ $note->title }}"><br>

                <label for="editor">内容 (Markdown)</label>
                <textarea id="editor" name="content">{{ $note->content }}</textarea>
                <div class="button_box">
                    <button type="submit" class="edit_keep_button">保存</button>
                    <div style="margin-top:20px;">
                        <input type="file" id="imageUpload" style="display:none;">
                        <button type="button" id="imageUploadButton">画像アップロード</button>
                    </div>
                </div>
            </div>

            <!-- 下段: ラベルとプレビューを横並び -->
            <div class="editor-bottom" >
                <!-- ラベル -->
                <div class="label-column" >
                    <label>ラベル</label>
                    <div class="label_box">
                        @foreach($labels as $label)
                            <label style="background-color: {{ $label->color ?? '#cccccc' }}; padding: 0.2em 0.5em; border-radius: 4px; color: #fff;">
                            <input type="checkbox" name="labels[]" value="{{ $label->id }}"
                                {{ isset($note) && $note->labels->contains($label->id) ? 'checked' : '' }}>
                            {{ $label->name }}
                        </label><br>
                        @endforeach
                    </div>
                </div>

                <!-- プレビュー -->
                <div class="preview-column" style="flex:2;">
                    <label for="preview">プレビュー</label>
                    <div id="preview" ></div>
                    <button class="edit_back_button"><a href="{{ route('note') }}">戻る</a></button>
                </div>
                
            </div>
        </div>
    </form>
    <!-- 保存ボタン -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/markdown/markdown.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>
        var editor = CodeMirror.fromTextArea(document.getElementById("editor"), {
            mode: "markdown",
            lineNumbers: true,
            theme: "idea",
            lineWrapping: true,
            autofocus: true
        });
        editor.setSize("650px", "580px");

        var preview = document.getElementById("preview");

        function updatePreview() {
            preview.innerHTML = marked.parse(editor.getValue());
        }
        editor.on("change", updatePreview);
        updatePreview();

        // フラグで無限ループ防止
        var isSyncingEditor = false;
        var isSyncingPreview = false;

        // エディタ → プレビュー
        editor.getScrollerElement().addEventListener('scroll', function() {
            if (isSyncingPreview) return;
            isSyncingEditor = true;
            var scrollInfo = editor.getScrollInfo();
            var scrollRatio = scrollInfo.top / (scrollInfo.height - scrollInfo.clientHeight);
            preview.scrollTop = scrollRatio * (preview.scrollHeight - preview.clientHeight);
            isSyncingEditor = false;
        });

        // プレビュー → エディタ
        preview.addEventListener('scroll', function() {
            if (isSyncingEditor) return;
            isSyncingPreview = true;
            var scrollRatio = preview.scrollTop / (preview.scrollHeight - preview.clientHeight);
            var scrollInfo = editor.getScrollInfo();
            editor.scrollTo(null, scrollRatio * (scrollInfo.height - scrollInfo.clientHeight));
            isSyncingPreview = false;
        });

        editor.on("cursorActivity", function() {
            const content = editor.getValue();
            const from = editor.getCursor("from");
            const to = editor.getCursor("to");

            const start = editor.indexFromPos(from);
            const end = editor.indexFromPos(to);

            let highlighted = content.substring(0, start)
                + "<mark>" + content.substring(start, end) + "</mark>"
                + content.substring(end);

            document.getElementById("preview").innerHTML = marked.parse(highlighted);
        });

    function updatePreview() {
        preview.innerHTML = marked.parse(editor.getValue());
    }

    editor.on("change", updatePreview);
    updatePreview();

    // 画像アップロードボタン
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

        fetch('{{ route("image.upload") }}', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.url) {
                // Markdown に挿入
                const markdown = `\n![](${data.url})\n`;
                const doc = editor.getDoc();
                const cursor = doc.getCursor();
                doc.replaceRange(markdown, cursor);
                updatePreview();
            } else {
                alert('アップロード失敗');
            }
        })
        .catch(err => console.error(err));
    });

    const form = document.getElementById('noteForm');
    form.addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const content = editor.getValue().trim(); // CodeMirror の内容取得

        if(title === '') {
            e.preventDefault();
            alert('タイトルを入力してください');
            return;
        }

        if(content === '') {
            e.preventDefault();
            alert('内容を入力してください');
            return;
        }

        // CodeMirror の内容を textarea に書き戻す
        document.getElementById('editor').value = content;
    });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laravel Api With Axios</title>
    {{-- fontawesome css --}}
    <link rel="stylesheet" href="{{ asset('assets/css/all.min.css') }}">
    {{-- bootstrap css --}}
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
</head>

<body>
    <div class="container">
        <div class="row my-5">
            <div class="col-12">
                <div class="row">
                    {{-- Post Table Section --}}
                    <div class="col-md-8 px-2">
                        <div id="successMsg"></div>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="tbody">
                            </tbody>
                        </table>
                    </div>
                    {{-- Post Create Form --}}
                    <div class="col-md-4">
                        <form name="createForm">
                            <div class="mb-3">
                                <label for="name">Name</label>
                                <input type="text" name="name" id="name" class="form-control"
                                    placeholder="Name...">
                                <span id="nameError"></span>
                            </div>
                            <div class="mb-3">
                                <label for="desc">Description</label>
                                <textarea name="description" id="desc" class="form-control" placeholder="Description..."></textarea>
                                <div id="descError"></div>
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-success" name="createBtn">Create</button>
                            </div>
                        </form>
                    </div>

                    {{-- Edit Modal --}}
                    <div class="modal fade" id="editModal">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Edit Post</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form name="editForm" id="updateModal">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="name">Name</label>
                                            <input type="text" name="name" id="name" class="form-control"
                                                required>
                                            <span id="editNameError"></span>
                                        </div>
                                        <div class="mb-3">
                                            <label for="desc">Description</label>
                                            <textarea name="description" id="desc" class="form-control" required></textarea>
                                            <span id="editDescError"></span>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
{{-- axios js --}}
<script src="{{ asset('assets/js/axios.min.js') }}"></script>
{{-- jquery js --}}
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script>
    const createForm = document.forms['createForm'];
    const name = createForm['name'];
    const description = createForm['description'];
    const tbody = document.querySelector('#tbody');

    //for update and delete
    let idList = document.getElementsByClassName('idList');
    let nameList = document.getElementsByClassName('nameList');
    let descList = document.getElementsByClassName('descList');
    let btnList = document.getElementsByClassName('btnList');

    // Read Data
    axios.get('/api/posts')
        .then(response => {
            response.data.posts.forEach(post => {
                displayData(post);
            });
        })

    // Create
    createForm.onsubmit = function(e) {
        e.preventDefault();

        let nameValue = name.value;
        let descriptionValue = description.value;

        const nameError = document.querySelector('#nameError');
        const descError = document.querySelector('#descError');

        axios.post('/api/posts', {
            'name': nameValue,
            'description': descriptionValue
        }).then(response => {
            // for success
            if (response.data.msg == 'Post created successfully!') {
                displayData(response.data.post);

                createForm.reset();
                showSuccessMsg(response.data);
                nameError.innerHTML = descError.innerHTML = '';
            } else {
                nameError.innerHTML = nameValue == '' ? `
            <p class="text-danger">${response.data.msg.name}</p>
            ` : '';
                descError.innerHTML = descriptionValue == '' ? `
            <p class="text-danger">${response.data.msg.description}</p>
            ` : '';
            }
        })
    }

    const editForm = document.forms['editForm'];
    let editName = editForm['name'];
    let editDescription = editForm['description'];
    let postId = document.getElementsByClassName('postId'); // for edit and delete
    let postIdToUpdate = 0;
    // Edit
    function editFunc(id) {
        postIdToUpdate = id;
        axios.get('/api/posts/' + id)
            .then(response => {
                editName.value = response.data.post.name;
                editDescription.value = response.data.post.description;
            })
            .catch(error => {
                console.log(error)
            })
    }

    // Update
    editForm.onsubmit = function(event) {
        event.preventDefault();

        axios.put('/api/posts/' + postIdToUpdate, {
                name: editName.value,
                description: editDescription.value
            })
            .then(response => {
                for (let i = 0; i < postId.length; i++) {
                    if (parseInt(postId[i].value) == postIdToUpdate) {
                        nameList[i].innerHTML = editName.value;
                        descList[i].innerHTML = editDescription.value;
                    }
                }

                $('#editModal').modal('hide');
                showSuccessMsg(response.data);
            })
    }

    // Delete
    function deleteFunc(id) {
        if (confirm('Sure to delete')) {
            axios.delete('/api/posts/' + id)
                .then(response => {
                    for (let i = 0; i < postId.length; i++) {
                        if (parseInt(postId[i].value) == response.data.post.id) {
                            idList[i].style.display = nameList[i].style.display = descList[i].style.display =
                                btnList[i].style.display = 'none';
                        }
                    }

                    showSuccessMsg(response.data);
                })
        }
    }

    // displayData
    function displayData(post) {
        tbody.innerHTML += `
                <tr>
                    <td class="idList">
                        ${post.id}
                        <input type="hidden" class="postId" value="${post.id}">
                    </td>
                    <td class="nameList">${post.name}</td>
                    <td class="descList">${post.description}</td>
                    <td class="btnList">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#editModal" onclick="editFunc(${post.id})" class="btn btn-sm btn-secondary">Edit</button>
                        <button type="button" onclick="deleteFunc(${post.id})" class="btn btn-sm btn-danger">Delete</button>
                    </td>
                </tr>
                `;
    }

    // show success msg
    function showSuccessMsg(data) {
        document.querySelector('#successMsg').innerHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>${data.msg}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    </button>
                </div>
                `;

    }
</script>

</html>

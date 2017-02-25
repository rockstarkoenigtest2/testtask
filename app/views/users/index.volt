
<script>
    function createEditModal(action, headers, keys, values) {
        var modalBody = $('<div id="modalContent"></div>');
        var modalForm = $('<form role="form" id="modalForm" name="modalForm" action="" method="post"></form>');
        modalForm.append('<div id="modalError" class="alert alert-danger" hidden></div>');

        var groups = {{ groups|json_encode }};
        $.each(keys, function (i, key) {
            var formGroup;
            var header = headers[i];

            if (key == 'groups') {
                var userGroups = values ? values[i].split(',') : [];
                formGroup = $('<div class="form-group"></div>');
                formGroup.append('<label for="groups[]">' + header + '</label><br>');
                modalForm.append(formGroup);

                for (var j = 0; j < groups.length; j++ ) {
                    var checked = userGroups.indexOf(groups[j].name) != -1;
                    formGroup.append('<div class="checkbox"><label><input type="checkbox" name="groups[]" value="' + groups[j].id + '"' + (checked ? "checked" : "") + '> ' + groups[j].name + '</label></div>');
                }

                return true;
            }
            formGroup = $('<div class="form-group"></div>');
            formGroup.append('<label for="' + key + '">' + header + '</label>');
            var input = $('<input autocomplete="off" class="form-control" name="' + key + '" id="' + key + '" />');
            if (values) {
                input.val(values[i]);
            }
            if (key == 'password' || key == 'repeatPassword') {
                input.prop("type", "password");
                input.prop("autocomplete", "new-password");
            }
            formGroup.append(input);

            if (key == 'id') {
                input.prop('readonly', true);
            }
            modalForm.append(formGroup);
        });

        modalForm.on('submit', function(e){
            $.ajax({
                type: "POST",
                url: '{{ url('users') }}/' + action,
                data: $('form[name="modalForm"]').serialize(),
                success: function(data)
                {
                    var dataObject = JSON.parse(data);
                    if (dataObject.hasOwnProperty("error")) {
                        $('#modalError').show();
                        $('#modalError').text(dataObject.error);
                    } else {
                        if (action == "create") {
                            $(location).attr('href', '{{ url('users') }}' + '?page=last')
                        } else {
                            location.reload();
                        }

                    }
                }
            });

            e.preventDefault();
        })

        return modalForm;
    }

    $(document).ready(function () {
        // On Create Button
        $("#createButton").click(function() {
            var modalBody = $('<div id="modalContent"></div>');
            var modalForm = createEditModal("create", ["Name", "Email", "Password", "Repeat", "Groups"], ["name", "email", "password", "repeatPassword", "groups"]);
            modalBody.append(modalForm);
            $('#userDataModalBody').html(modalBody);

            $('#submitButton').text("Create");
        });

        // On Edit Button
        $(".editButton").click(function() {
            var headers = $("thead th").map(function () {
                return $(this).text();
            }).get();

            var keys = $("thead th").map(function () {
                return $(this).data("key");
            }).get();
            var modalBody = $('<div id="modalContent"></div>');

            var values = $(this).parent().siblings().map(function () {
                return $(this).text();
            }).get();
            var modalForm = createEditModal("update", headers, keys, values);
            modalBody.append(modalForm);
            $('#userDataModalBody').html(modalBody);
            $('#submitButton').text("Update");
        });

        // On Delete Button
        $(".deleteButton").click(function() {
            $('#deleteConfirmModalBody').html("Are you really want to remove user`s record? This procedure is irreversible.");
            $('#deleteConfirmButton').data("pk", $(this).data("pk"));
        });

        // On Delete Confirm Button
        $("#deleteConfirmButton").click(function() {
            $.ajax({
                type: "POST",
                url: '{{ url('users') }}' + '/delete',
                data: "id=" + $(this).data("pk"),
                success: function(data)
                {
                    location.reload();
                }
            });
        });

        $('#submitButton').click(function () {
            $('form[name="modalForm"]').submit();
        });
    });

</script>


{# Nav Bar #}
<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <p class="navbar-text">Hi, {{ session.get("name") }}!</p>
            </ul>
            <form class="navbar-form navbar-left" action="{{ url('users') }}">
                <div class="form-group">
                    <input type="text" name="search" class="form-control" placeholder="Search">
                </div>
                <button type="submit" class="btn btn-default">Go</button>
            </form>

            <ul class="nav navbar-nav navbar-right">
            {% if session.get("can_edit") %}
                <li>
                    <p class="navbar-btn"><a href="#" id="createButton" class="btn btn-default" data-toggle="modal" data-target="#userDataModal">Create new user</a></p>
                </li>
            {% endif %}
            <li><a href="{{ url('users') }}/logout">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
<br>

{{ content() }}

<div>
    {% if totalCount == 0 %}
        {{ (query is empty) ? 'There are no users in database. So how could you sign in? ;)' : 'No results for "' ~ query ~ '".' }}
    {% else %}
        {{ (query is empty) ? 'All users:' : 'Search results for "' ~ query ~ '" (' ~ totalCount ~ '):' }}
    {% endif %}

</div>
</br>

{# Table #}
{% if totalCount > 0 %}
    <table class="table table-responsive">
        <thead>
            <tr>
                <th data-key="id" class="">ID</th>
                <th data-key="name" class="">Name</th>
                <th data-key="email" class="">Email</th>
                <th data-key="groups" class="">Group(s)</th>
                {% if session.get("can_edit") %}
                    <th class=""></th>
                {% endif %}
            </tr>
        </thead>

        <tbody>
            {% for item in page.items %}
                <tr>
                    <td style="text-align:center;" class="">{{ item.id }}</td>
                    <td class="">{{ item.name }}</td>
                    <td>{{ item.email }}</td>
                    <td>{{ item.group_names }}</td>
                    {% if session.get("can_edit") %}
                        <td>
                            <button class="btn btn-warning editButton" data-toggle="modal" data-target="#userDataModal" contenteditable="false">Edit</button>
                            <button class="btn btn-danger deleteButton" data-pk="{{ item.id }}" data-toggle="modal" data-target="#confirm-delete" contenteditable="false">Delete</button>
                        </td>
                    {% endif %}
                </tr>
            {% endfor %}
        </tbody>
    </table>
{% endif%}

{# User data modal #}
<div class="modal fade" id="userDataModal" tabindex="-1" role="dialog" aria-labelledby="userDataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="userDataModalLabel">User`s data</h4>
            </div>
            <div id="userDataModalBody" class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button id="submitButton" type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

{# Delete confirm dialog #}
<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="userDataModalLabel">Confirm delete</h4>
            </div>
            <div id="deleteConfirmModalBody" class="modal-body">
                Are you really want to remove user`s record? This procedure is irreversible.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button id="deleteConfirmButton" class="btn btn-danger btn-ok">Delete</button>
            </div>
        </div>
    </div>
</div>


{# Pagination #}
{% if totalCount > page.limit %}
    <ul class="pagination">
        {% for i in 1..page.total_pages %}
            {% if i == page.current %}
                <li class="disabled"><a href="#">{{ i }}</a></li>
            {% else %}
                <li>{{ link_to(pagingUrl ~ i, i) }}</li>
            {% endif %}
        {% endfor %}
    </ul>
{% endif %}

{% if not(query is empty) %}
    <p><a href="{{ url('users') }}" class="btn btn-info">Back to all users</a></p>
{% endif %}

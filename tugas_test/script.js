function fetchData() {
    fetch('api.php?action=getUsers')
        .then(response => response.json())
        .then(data => {
            const userTable = document.getElementById('userTable');
            userTable.innerHTML = '';
            data.forEach(user => {
                const row = `
                    <tr>
                        <td>${user.id}</td>
                        <td>${user.name}</td>
                        <td>${user.email}</td>
                        <td>${user.gender}</td>
                        <td>${user.departement}</td>
                        <td><img src="${user.image}" alt="User Image" width="50"></td>
                    </tr>`;
                userTable.innerHTML += row;
            });
        })
        .catch(error => console.error('Error:', error));
}

function addUser() {
    const formData = new FormData(document.getElementById('userForm'));

    fetch('api.php?action=addUser', {
        method: 'POST',
        body: formData,
    })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            fetchData(); // Refresh user list
        })
        .catch(error => console.error('Error:', error));
}

// Fetch data on page load
document.addEventListener('DOMContentLoaded', fetchData);

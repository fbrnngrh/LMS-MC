$(document).ready(function () {
    $(document).on('click', '#delete', function (e) {
      e.preventDefault();
  
      var link = $(this).attr("href");
  
      Swal.fire({
        title: 'Are you sure?',
        text: "Delete this data?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {
          // Redirect to the delete link
          window.location.href = link;
          Swal.fire(
            'Deleted!',
            'Your file has been deleted.',
            'success'
          );
  
        } else {
          // Swal.fire with cancellation message (optional)
          Swal.fire(
            'Cancelled',
            'Your data is safe :)',
            'info'
          );
        }
      });
    });
  });
  
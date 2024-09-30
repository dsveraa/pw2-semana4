const dropdown = document.querySelector('.dropdown')
const countLabel = dropdown.querySelector('#count-label')

dropdown.addEventListener('click', deleteContent)

function deleteContent() {
    setTimeout(function() {
        countLabel.innerHTML = ""
    }, 1000)
}

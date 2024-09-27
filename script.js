const countLabel = document.getElementById('count-label')

countLabel.addEventListener('click', deleteContent)

function deleteContent() {
    setTimeout(function() {
        countLabel.innerHTML = ""
    }, 1000)
}

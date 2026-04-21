document.addEventListener("DOMContentLoaded", () => {

    const btn = document.querySelector("#comment-submit")
    const list = document.querySelector("#comments-list")


    if(!btn || !list) return

    let loading = false

    btn.addEventListener("click", () => {
        if(loading) return

        const name = document.querySelector("#comment-name").value
        const text = document.querySelector("#comment-text").value
        const hp = document.querySelector("#comment-hp").value
        const time = document.querySelector("#comment-time").value
        const countEl = document.querySelector("#comments-count")

        if(!text) return
        loading = true
        const formData = new FormData()

        formData.append("action","add_comment")
        formData.append("post_id", comment_ajax.post_id)
        formData.append("author", name)
        formData.append("comment", text)

        formData.append("hp", hp)
        formData.append("time", time)

        fetch(comment_ajax.url,{
            method:"POST",
            body:formData
        })
            .then(res => res.text())
            .then(html => {

                loading = false

                list.insertAdjacentHTML("afterbegin", html)

                const newComment = list.firstElementChild

                requestAnimationFrame(() => {
                    newComment.classList.add("animated")
                })

                if(countEl){
                    countEl.textContent = parseInt(countEl.textContent) + 1
                }

                document.querySelector("#comment-text").value = ""
                document.querySelector("#comment-name").value = ""

            })
            .catch(() => {
                loading = false
            })

    })

})
document.addEventListener("DOMContentLoaded", () => {

    const btn = document.querySelector("#comment-submit")
    const list = document.querySelector("#comments-list")


    if(!btn || !list) return
    const loader = document.getElementById("ajax-loader")

    let loading = false
    loader.classList.remove("active")

    btn.addEventListener("click", () => {
        if(loading) return

        const name = document.querySelector("#comment-name").value
        const text = document.querySelector("#comment-text").value
        const hp = document.querySelector("#comment-hp").value
        const time = document.querySelector("#comment-time").value

        if(!text) return

        loading = true
        loader.classList.add("active")

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

                loader.classList.remove("active")
                loading = false

                list.insertAdjacentHTML("afterbegin", html)

                document.querySelector("#comment-text").value = ""
                document.querySelector("#comment-name").value = ""

            })
            .catch(() => {

                loader.classList.remove("active")
                loading = false

            })

    })

})
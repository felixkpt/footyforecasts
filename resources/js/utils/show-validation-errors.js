const showValidationErrors = errors => {
    if (errors && Object.keys(errors).length > 0) {

        for (let key in errors) {

            let input = key
            if (key == 'email') input = 'username'
            if (key == 'start_date') input = 's'
            if (key == 'end_date') input = 't'

            const element = document.getElementsByName(input)[0] || document.getElementsByName(key)[0]
            if (!element) continue

            element.classList.add('is-invalid')

            const invalidFeedback = document.createElement('div')
            invalidFeedback.classList.add('invalid-feedback')
            invalidFeedback.innerHTML = errors[key]
            const el = element.closest('.form-group')
            el.classList.add('has-error')
            el.append(invalidFeedback)

            element.addEventListener('focus', function () {
                element.classList.remove('is-invalid')
                invalidFeedback.remove()
            })
        }
    }
}

export default showValidationErrors
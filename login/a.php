<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Selects</title>
    <style>
        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .invalid-feedback {
            color: red;
            font-size: 12px;
            display: none;
        }
    </style>
</head>
<body>

<div class="form-group">
    <label for="faculty">Faculty <span>*</span></label>
    <select class="form-control faculty" name="faculty" id="faculty">
        <option value="0">All</option>
    </select>
    <div class="invalid-feedback">This field is required.</div>
</div>

<div class="form-group">
    <label for="program">Program <span>*</span></label>
    <select class="form-control program" name="program" id="program">
        <option value="0">All</option>
    </select>
    <div class="invalid-feedback">This field is required.</div>
</div>

<div class="form-group">
    <label for="session">Session <span>*</span></label>
    <select class="form-control session" name="session" id="session">
        <option value="0">All</option>
    </select>
    <div class="invalid-feedback">This field is required.</div>
</div>

<div class="form-group">
    <label for="semester">Semester <span>*</span></label>
    <select class="form-control semester" name="semester" id="semester">
        <option value="0">All</option>
    </select>
    <div class="invalid-feedback">This field is required.</div>
</div>

<div class="form-group">
    <label for="section">Section <span>*</span></label>
    <select class="form-control section" name="section" id="section">
        <option value="0">All</option>
    </select>
    <div class="invalid-feedback">This field is required.</div>
</div>

<script>
    // Simulated data
    const faculties = [
        { id: 1, title: 'Faculty of Science' },
        { id: 2, title: 'Faculty of Arts' }
    ];

    const programs = {
        1: [
            { id: 101, title: 'Physics' },
            { id: 102, title: 'Chemistry' }
        ],
        2: [
            { id: 201, title: 'History' },
            { id: 202, title: 'Literature' }
        ]
    };

    const sessions = [
        { id: 1, title: '2024' },
        { id: 2, title: '2025' }
    ];

    const semesters = [
        { id: 1, title: 'Semester 1' },
        { id: 2, title: 'Semester 2' }
    ];

    const sections = {
        1: [
            { id: 101, title: 'A' },
            { id: 102, title: 'B' }
        ],
        2: [
            { id: 201, title: 'C' },
            { id: 202, title: 'D' }
        ]
    };

    // Populate faculties
    const facultySelect = document.getElementById('faculty');
    faculties.forEach(faculty => {
        const option = document.createElement('option');
        option.value = faculty.id;
        option.textContent = faculty.title;
        facultySelect.appendChild(option);
    });

    // Handle faculty change
    facultySelect.addEventListener('change', function () {
        const selectedFaculty = this.value;
        const programSelect = document.getElementById('program');

        programSelect.innerHTML = '<option value="0">All</option>';
        if (programs[selectedFaculty]) {
            programs[selectedFaculty].forEach(program => {
                const option = document.createElement('option');
                option.value = program.id;
                option.textContent = program.title;
                programSelect.appendChild(option);
            });
        }
    });

    // Handle program change
    document.getElementById('program').addEventListener('change', function () {
        const sessionSelect = document.getElementById('session');
        sessionSelect.innerHTML = '<option value="0">All</option>';

        sessions.forEach(session => {
            const option = document.createElement('option');
            option.value = session.id;
            option.textContent = session.title;
            sessionSelect.appendChild(option);
        });
    });

    // Handle session change
    document.getElementById('session').addEventListener('change', function () {
        const semesterSelect = document.getElementById('semester');
        semesterSelect.innerHTML = '<option value="0">All</option>';

        semesters.forEach(semester => {
            const option = document.createElement('option');
            option.value = semester.id;
            option.textContent = semester.title;
            semesterSelect.appendChild(option);
        });
    });

    // Handle semester change
    document.getElementById('semester').addEventListener('change', function () {
        const selectedSemester = this.value;
        const sectionSelect = document.getElementById('section');

        sectionSelect.innerHTML = '<option value="0">All</option>';
        if (sections[selectedSemester]) {
            sections[selectedSemester].forEach(section => {
                const option = document.createElement('option');
                option.value = section.id;
                option.textContent = section.title;
                sectionSelect.appendChild(option);
            });
        }
    });
</script>

</body>
</html>

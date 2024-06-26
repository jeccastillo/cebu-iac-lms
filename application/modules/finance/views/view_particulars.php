<aside class="right-side"
    id="registration-container">
    <section class="content-header">
        <h1>
            <?php echo strtoupper($type); ?>
        </h1>
    </section>
    <hr />
    <div class="content">
        <div class="box box-primary">
            <div class="box-header">
                <h4><?php echo strtoupper($type); ?></h4>
            </div>
            <div class="box-body">

                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="items.length == 0">
                            <td colspan='3'>No Items Found</td>
                        </tr>
                        <tr v-else
                            v-for="(item,index) in items">
                            <td>{{ index+1 }}</td>
                            <td>{{ item.name }}</a></td>
                            <td><button class="btn btn-primary"
                                    @click="removeItem(index)">Delete Item</button></td>
                        </tr>
                        <tr>
                            <td colspan='3'>
                                <button class="btn btn-primary"
                                    @click="openModal()">Add Item</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <dialog>
            <div>
                <form v-on:submit.prevent="submit()">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Name</label>
                        <input type="text"
                            class="form-control"
                            v-model="nameObj.name"
                            placeholder="Name">
                    </div>
                    <button type="submit"
                        class="btn btn-default">Submit</button>
                </form>
            </div>
        </dialog>
    </div>

</aside>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript"
    src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>
new Vue({
    el: '#registration-container',
    data: {
        base_url: '<?php echo base_url(); ?>',
        type: '<?php echo $type; ?>',
        active_sem: undefined,
        items: [],
        dialogEl: '',
        nameObj: {
            name: ''
        }


    },

    mounted() {
        this.dialogEl = document.querySelector("dialog");

        let url_string = window.location.href;
        if (this.id != 0) {
            //this.loader_spinner = true;
            axios.get(this.base_url + 'finance/view_particulars_data/' + this.type)
                .then((data) => {
                    this.terms = data.data.sy;
                    this.sem = data.data.active_sem.intID;
                    this.active_sem = data.data.active_sem;
                    this.students = data.data.students;
                })
                .catch((error) => {
                    console.log(error);

                });
        }

    },

    methods: {
        openModal() {
            this.dialogEl.show()
        },
        submit() {
            axios.post(this.base_url + 'finance/add_particular/' + this.type, this.nameObj)
                .then((data) => {
                    console.log(data);
                })
                .catch((error) => {
                    console.log(error);

                });
        },
        removeItem(index) {
            axios.post(this.base_url + `finance/delete_particular/${index}`)
                .then((data) => {
                    console.log(data);
                })
                .catch((error) => {
                    console.log(error);

                });
        }
    }

})
</script>
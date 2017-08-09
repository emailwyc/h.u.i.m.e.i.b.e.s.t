# vim: set fileencoding=utf-8 :

from .. import *
from ...models import User, Doctor, DoctorAssistant, DoctorArticle, Hospital, Region,Department, Setting
from ...forms.admin.doctor import CreateForm, UpdateForm, ArticleForm
from ...libs.oss.oss import OSS
from ...libs.easemob import Easemob
from flask import render_template, redirect, url_for, flash
from werkzeug import secure_filename
import copy
import hashlib
import os
from itertools import cycle
from ...libs.submail.app_configs import MESSAGE_CONFIGS
from ...libs.submail.message_xsend import MESSAGEXsend


admin_doctor = Blueprint('admin_doctor', __name__, url_prefix='/admin/doctor')


@admin_doctor.route('/list', methods=['GET'])
@http_auth_required
def doctor_list():
    doctors = Doctor.objects().order_by('+created_at')
    if doctors:
        for doctor in doctors:
            doctor.freeze = '不显示' if doctor.freeze == 'yes' else '显示'
            if doctor.region_id:
                region = Region.objects(id=doctor.region_id).first()
                doctor.region_id = region.name
            if doctor.region_child_id:
                region_child = Region.objects(id=doctor.region_child_id).first()
                doctor.region_child_id = region_child.name
            if doctor.assistant:
                assistant = DoctorAssistant.objects(id=doctor.assistant).first()
                if assistant:
                    doctor.assistant = assistant.name
            doctor.articles = DoctorArticle.objects(doctor=doctor).count()
       
    return render_template('admin/doctor/list.html', doctors=doctors)


@admin_doctor.route('/show/<id>', methods=['GET'])
@http_auth_required
def doctor_show(id):
    doctor = Doctor.objects(id=id).first_or_404()
    if doctor:
        user = doctor.uref
        if not isinstance(user, User):
            abort(404)
        if doctor.avatar and doctor.avatar.startswith('http://hm-img.huimeibest.com/avatar/'):
            if not doctor.avatar.endswith('@!256'):
                doctor.avatar += '@!256'
                doctor.save()
                doctor.reload()
        doctor.mobile = user.mobile
        doctor.freeze = '不显示' if doctor.freeze == 'yes' else '显示'
        if doctor.department_id:
            department = Department.objects(id=doctor.department_id).first()
            if department:
                doctor.department_id = department.name
        if doctor.department_child_id:
            department = Department.objects(id=doctor.department_child_id).first()
            if department:
                doctor.department_child_id = department.name
        if doctor.assistant:
            assistant = DoctorAssistant.objects(id=doctor.assistant).first()
            if assistant:
                doctor.assistant = '{0} {1}'.format(assistant.mobile, assistant.name)
        doctor.articles = DoctorArticle.objects(doctor=doctor).count()
        articles = DoctorArticle.objects(doctor=doctor)

    return render_template('admin/doctor/show.html', doctor=doctor, articles=articles)


@admin_doctor.route('/create', methods=['GET', 'POST'])
@http_auth_required
def doctor_create():
    form = CreateForm()
    form_init(form)
    try:
        if form.validate_on_submit():
            user = sign_up(form)
            upload_avatar(form)
            # locations_encode(form)
            doctor = doctor_info(form, user)
            flash('医生 {0} 添加成功'.format(form.data.get('name')))
            return redirect('/admin/doctor/show/{0}'.format(doctor.id))
        # print(form.errors)
        # {'csrf_token': ['CSRF token missing']}
        # {'avatar': ['只支持 jpg, png 格式的图片']}
        # {'avatar': ['头像必填']}
    except Exception as e:
        print(e)

    # locations_decode(form)
    return render_template('admin/doctor/form.html', create=True, form=form)


@admin_doctor.route('/update/<id>', methods=['GET', 'POST'])
@http_auth_required
def doctor_update(id):
    form = None
    try:
        form = UpdateForm()
        form_init(form)
        if request.method == 'POST' and form.validate_on_submit():
            user = target_user(form)
            upload_avatar(form)
            # locations_encode(form)
            doctor = doctor_info(form, user)
            flash('医生 {0} 修改成功'.format(form.data.get('name')))
            return redirect('/admin/doctor/show/{0}'.format(doctor.id))
    except Exception as e:
        print(e)

    if request.method == 'GET':
        doctor = Doctor.objects(id=id).first_or_404()
        if doctor:
            doctor.mobile = doctor.uref.mobile
            form = UpdateForm(obj=doctor)
            form_init(form)

    # locations_decode(form)
    return render_template('admin/doctor/form.html', create=False, form=form)


def form_init(form):
    hospitals = Hospital.objects().order_by('-id')
    choices = [('', '请选择医院')]
    if hospitals:
        for x in hospitals:
            choices.append((x.name, x.name))
        form.hospital.choices = choices
    department_ids = Department.objects(parent__exists=False).order_by('-id')
    choices = [('', '请选择一级科室')]
    if department_ids:
        for x in department_ids:
            choices.append((str(x.id), x.name))
        form.department_id.choices = choices

    department_id = form.data.get('department_id')
    choices = [('', '请先选择一级科室')]
    if department_id:
        department_ids = department_child_ids(department_id)
        if department_ids:
            for x in department_ids:
                choices.append((str(x.id), x.name))
    form.department_child_id.choices = choices

    assistant_list = DoctorAssistant.objects().order_by('+id')
    choices = [('', '请选择医生助理')]
    if assistant_list:
        for x in assistant_list:
            choices.append((str(x.id), '{0} {1}'.format(x.mobile, x.name)))
        form.assistant.choices = choices

    form.position.choices = [('主任医师', '主任医师'), ('副主任医师', '副主任医师'),
                             ('主治医师', '主治医师'), ('医师', '医师'),  ('住院医师', '住院医师')]
    form.title.choices = [('专家', '专家'), ('教授', '教授'), ('副教授', '副教授')]


def department_child_ids(parent):
    if not parent:
        return None
    try:
        from bson import ObjectId
        parent = ObjectId(parent)
        return Department.objects(parent=parent).order_by('-id')
    except Exception as e:
        return None


def target_user(form):
    mobile = form.data.get('mobile')
    url_segments = request.path.strip('/').split('/')
    if len(url_segments) != 4:
        form.mobile.errors.append('用户不存在')
        raise ResourceWarning('用户不存在')
    uid = url_segments[3]
    user = User.objects(id=uid).first()
    if not user:
        form.mobile.errors.append('用户不存在')
        raise ResourceWarning('用户不存在')
    if user.mobile != mobile:
        if User.objects(mobile=mobile):
            form.mobile.errors.append('手机号码已经注册')
            raise ResourceWarning('手机号码已经注册')
        else:
            user.mobile = mobile
            password = password_md5(user.mobile[5:])
            user.salt = helper.salt()
            user.password = helper.password_encrypt(password, user.salt)
            user.save()
    init_user_for_chat(user.id)
    return user


def password_md5(plaintext):
    md5sum = hashlib.md5()
    md5sum.update(plaintext.encode('utf-8'))
    return md5sum.hexdigest()


def sign_up(form):
    mobile = form.data.get('mobile')
    user = User.objects(mobile=mobile).first()
    if user and Doctor.objects(uref=user):
        form.mobile.errors.append('手机号码已经注册')
        raise ResourceWarning('手机号码已经注册')
    if not user:
        user = User()
        user.mobile = mobile

    password = password_md5(user.mobile[5:])
    user.salt = helper.salt()
    user.password = helper.password_encrypt(password, user.salt)
    user.save()
    user.reload()
    init_user_for_chat(user.id)
    return user


def upload_avatar(form):
    if form.avatar.data.content_type == 'application/octet-stream':
        form.avatar.data = None
        return

    image_types = ('jpeg', 'png', 'gif')
    def _write(path, stream):
        with open(path, 'wb') as image:
            image.write(stream)
            return True
        return False

    stream = form.avatar.data.read()

    ext = 'jpg'
    if form.avatar.data.content_type == 'image/png':
        ext = 'png'
    m = hashlib.md5()
    m.update(stream)
    prefix = '/dev/shm'
    filename = '{0}.{1}'.format(m.hexdigest(), ext)
    path = '{0}/{1}'.format(prefix, filename)
    _write(path, stream)

    # filename = secure_filename(form.avatar.data.filename)
    # avatar = form.avatar.data.save('/dev/shm/' + filename)

    host = 'oss-cn-beijing-internal.aliyuncs.com'
    key_id = 'uLmwkyi2tLw0pj7L'
    key_secret = 'DnNH0hXvDV2zqlf9HaCNrNpLwOBXIb'
    oss = OSS(host, key_id, key_secret).bucket('hm-img').set_prefix('avatar/')
    _path = oss.upload(filename, path)
    _prefix = 'http://hm-img.huimeibest.com/'
    form.avatar.data = '{0}{1}{2}'.format(_prefix, _path, '@!256')
    print('uploaded doctor avatar: {0}'.format(form.avatar.data))
    os.remove(path)


def locations_encode(form):
    locations = form.data.get('locations').split('\r\n')
    locations = [x.strip(' ') for x in locations]
    locations = list(set(locations))
    if '' in locations:
        locations.remove('')
    form.locations.data = locations


def locations_decode(form):
    locations = form.data.get('locations')
    if locations and isinstance(locations, list):
        locations = '\r\n'.join(locations)
        form.locations.data = locations


def doctor_info(form, user):
    doctor = Doctor.objects(id=user.id).first()
    if doctor is None:
        doctor = Doctor()
        doctor.id = user.id
        doctor.uref = user
        _set_doctor_default(doctor)

    for k, v in form.data.items():
        if k == 'avatar' and v == None:
            continue
        setattr(doctor, k, v)

    hospital_name = doctor.hospital
    hospital = Hospital.objects(name=hospital_name).first()
    if hospital:
        doctor.hospital_id = hospital.id
        doctor.region_id=hospital.region_id
        doctor.region_child_id=hospital.region_child_id 
    else:
        form.hospital.errors.append('医院不存在')
        raise ResourceWarning('医院不存在')

    doctor.updated_at = datetime.utcnow()
    doctor.save()

    return doctor


def init_user_for_chat(username):
    config = app.config.get('EASEMOB')
    username = str(username)

    def _init_user(cached, auth):
        instance = Easemob(config=config, auth=auth)

        print('checking user exists: {0}'.format(username))
        success, result = instance.get_user(username)
        if not success:
            password = helper.open_password(username)
            print('registering new user: {0}'.format(username))
            success, result = instance.register_user(username, password)
        else:
            print('checking user exists: {0} {1}'.format(username, 'exists'))
            pass

        auth_current = instance.app_client_auth.get_auth()
        if auth != auth_current:
            cached.token = auth_current.get('token')
            cached.application = auth_current.get('application')
            cached.expiring_at = str(auth_current.get('expiring_at'))
            cached.save()
            # print('--- save key-easemob-auth to cache: {0}'.format(auth_current))

    cached = Setting.objects(sign='huanxin').first()
    auth = {
        'token': cached.token,
        'application': cached.application,
        'expiring_at': int(cached.expiring_at),
    }
    # print('--- read key-easemob-auth from cache: {0}'.format(auth))

    try:
        _init_user(cached, auth)
    except PermissionError:
        try:
            print('--- --- --- cache invalid, recall without auth --- --- ---')
            _init_user(cached, None)
        except:
            pass


@admin_doctor.route('/department_child', methods=['GET'])
@http_auth_required
def department_child():
    parent = request.args.get('parent')
    from flask import jsonify
    from bson import ObjectId
    choices = {}
    try:
        parent = ObjectId(parent)
        departments = Department.objects(parent=parent)
        if departments:
            for x in departments:
                choices.update({str(x.id): x.name})
    finally:
        # return json.dumps(choices)
        return jsonify(choices)


@admin_doctor.route('/article/<id>', methods=['GET', 'POST'])
@http_auth_required
def doctor_article(id):
    form = None
    doctor = Doctor.objects(id=id).first_or_404()
    try:
        form = ArticleForm()
        if request.method == 'POST' and form.validate_on_submit():
            articles = articles_encode(doctor, form)
            articles_save(doctor, articles)
            flash('患教文章更新成功')
            return redirect('/admin/doctor/show/{0}'.format(id))
    except Exception as e:
        print(e)

    if request.method == 'GET':
        form = ArticleForm(obj=doctor)

    articles_decode(form)
    articles_load(doctor, form)
    return render_template('admin/doctor/article.html', id=id, form=form)


def articles_encode(doctor, form):
    image_urls = [
        'http://hm-img.huimeibest.com/article/demo-01.png',
        'http://hm-img.huimeibest.com/article/demo-02.png',
        'http://hm-img.huimeibest.com/article/demo-03.png',
    ]
    if doctor.avatar and doctor.avatar.startswith('http://'):
        image_urls = [doctor.avatar]
    image_urls = cycle(image_urls)
    if not form.data.get('articles'):
        return []
    try:
        data_list = [x.strip(' ') for x in form.data.get('articles').split('\r\n')]
        data_text = '\n'.join(data_list)
        while '\n\n\n' in data_text:
            data_text = data_text.replace('\n\n\n', '\n\n')
        data_list = data_text.split('\n')
        articles = [data_list[i:i+5] for i in range(0, len(data_list), 5)]
        data = []
        for article in articles:
            if len(article) == 5:
                article.pop()
            title, posted_date, link_url, description = article
            image_url = next(image_urls)
            data.append({
                'title': title,
                'posted_date': posted_date,
                'link_url': link_url,
                'image_url': image_url,
                'description': description
            })
        form.articles.data = data_list
        return data
    except Exception as e:
        form.articles.errors.append('数据格式错误')
        raise ResourceWarning('数据格式错误')


def articles_decode(form):
    articles = form.data.get('articles')
    if articles and isinstance(articles, list):
        articles = '\r\n'.join(articles)
        form.articles.data = articles


def articles_save(doctor, articles):
    DoctorArticle.objects(doctor=doctor).delete()
    for item in articles:
        article = DoctorArticle()
        article.doctor = doctor
        article.title = item.get('title')
        article.posted_date = item.get('posted_date')
        article.link_url = item.get('link_url')
        article.image_url = item.get('image_url')
        article.description = item.get('description')
        article.updated_at = datetime.utcnow()
        article.save()


def articles_load(doctor, form):
    if form.articles.data:
        return
    articles = DoctorArticle.objects(doctor=doctor)
    data = []
    for article in articles:
        data.append('\r\n'.join([article.title, article.posted_date, article.link_url, article.description]))
    articles = '\r\n\r\n'.join(data)
    form.articles.data = articles


def _set_doctor_default(doctor):
    default = {
        'service_provided': {
            "consult": Doctor._service_provided(on=False, price=100),
            "clinic": Doctor._service_provided(on=True),
            "phonecall": Doctor._service_provided(on=False, price_05=-1, price_10=-1, price_15=-1, price_20=-1, quantity=0, minutes_min=10000)
        },
        'starred': 0,
        'con_num': 0,
        'reg_num': 0,
        'rc_num': 0,
        'mul_num': 0,
        'level' : 0,
    }
    for k, v in default.items():
        setattr(doctor, k, v)


@admin_doctor.route('/bulk-info-register', methods=['GET'])
@http_auth_required
def __bulk_info_register():
    abort(404)

    def _sign_up(mobile):
        user = User.objects(mobile=mobile).first()
        if user:
            doctor = Doctor.objects(uref=user).first()
            if doctor:
                print('已经注册: {0} {1}'.format(mobile, doctor.name))
        else:
            user = User()
            user.mobile = mobile
        password = password_md5(user.mobile[5:])
        user.salt = helper.salt()
        user.password = helper.password_encrypt(password, user.salt)
        user.save()
        user.reload()
        init_user_for_chat(user.id)
        return user

    def _xcopy(doctor_current, doctor_template):
        for key in ('freeze', 'avatar', 'assistant', 'hospital', 'hospital_id', 'region_id', 'region_child_id', 'department', 'department_id', 'department_child_id', 'position', 'title', 'speciality', 'description'):
            value = getattr(doctor_template, key)
            # print('key: {0}, value: {1}'.format(key, value))
            setattr(doctor_current, key, value)
        doctor_current.updated_at = datetime.utcnow()
        doctor_current.save()

    def _doctor_info(user):
        doctor = Doctor.objects(id=user.id).first()
        if doctor is None:
            doctor = Doctor()
            doctor.id = user.id
            doctor.uref = user
        _set_doctor_default(doctor)
        return doctor

    doctor_template = Doctor.objects(id='562a3f6b83cdf872bd7b2a90').first()
    bulk_info = {
    }
    for mobile, name in bulk_info.items():
        print('mobile: {0}, name: {1}'.format(mobile, name))
        user = _sign_up(mobile)
        doctor_current = _doctor_info(user)
        doctor_current.name = name
        _xcopy(doctor_current, doctor_template)
    abort(400)


@admin_doctor.route('/freeze-mobile-list-and-force-reset-password', methods=['GET'])
def freeze_mobile_list():
    abort(404)

    def _reset_password(doctor):
        user = User.objects(id=doctor.id).first()
        if not user:
            print('用户不存在 {0} {1}'.format(doctor.uref.mobile, doctor.name))
            return
        last_6_of_mobile = password_md5(user.mobile[5:])
        salt = user.salt
        password = helper.password_encrypt(last_6_of_mobile, salt)
        if user.password != password:
            print('密码非手机号后6位 {0} {1}'.format(doctor.uref.mobile, doctor.name))
            _default_password_send_sms(user.mobile)
            # user.salt = helper.salt()
            # user.password = helper.password_encrypt(last_6_of_mobile, user.salt)
            # user.save()

    doctors = Doctor.objects(freeze__ne='yes')
    _list = []
    if doctors:
        try:
            for doctor in doctors:
                _list.append({doctor.uref.mobile: doctor.name})
                _reset_password(doctor)
        except AttributeError:
            print('{0} {1} AttributeError'.format(doctor.id, doctor.name))
    abort(400)


def _default_password_send_sms(mobile):
    submail = MESSAGEXsend(MESSAGE_CONFIGS)
    submail.set_project('w1bLX4')
    submail.add_to(mobile)
    x = submail.xsend()
    print('sms send: {0}'.format(mobile))


# vim:ts=4:sw=4

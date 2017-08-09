# vim: set fileencoding=utf-8 :

from .. import *
from ...models import Doctor, DoctorAssistant, DoctorQRcode, DoctorLocation, DoctorTimetable, DoctorPrivate, DoctorArticle, Order
from utils.week import Week
import datetime as dt

rpc_doctor = Blueprint('doctor', __name__, url_prefix='/rpc/doctor')


@rpc_doctor.route('/info', methods=['GET', 'POST'])
@rpc_doctor.route('/info/<attribute>', methods=['GET', 'POST'])
@rpc_doctor.route('/info/<attribute>/<sub_node>', methods=['GET', 'POST'])
@create_if_not_exist()
@auth_required('doctor')
def info(attribute=None, sub_node=None):

    if attribute not in (None, 'speciality', 'description', 'service_provided'):
        g.message = '接口请求错误'
        abort(400)

    if attribute == 'service_provided':
        if sub_node not in ('clinic', 'consult', 'phonecall'):
            g.message = '接口请求错误'
            abort(400)

    if request.method == 'POST':
        doctor = g.role_instance
        for k, v in g.form.items():
            if k == 'service_provided':
                v = handle_service_provided(v)
                if hasattr(doctor, k):
                    service_provided = getattr(doctor, k)
                    service_provided.update(v)
                    v = service_provided 
            setattr(doctor, k, v)
        doctor.updated_at = datetime.utcnow()
        doctor.save()
        doctor.reload()
        g.role_instance = doctor
        g.message = '设置成功'
    if attribute:
        return display({attribute: basic_info(g.role_instance, attribute=attribute, sub_node=sub_node)})
    else:
        return display({'doctor': basic_info(g.role_instance)})


def basic_info(doctor, attribute=None, sub_node=None):
    doctor = Doctor.objects(id=doctor.id).first()
    if not doctor:
        return None
    if not doctor.avatar:
        doctor.avatar = ''
    if doctor.avatar.startswith('http://hm-img.huimeibest.com/avatar/'):
        if not doctor.avatar.endswith('@!256'):
            doctor.avatar += '@!256'
            doctor.save()
            doctor.reload()

    if attribute:
        fields = [attribute]
        x = doctor.to_bson(fields=fields)
        if attribute == 'service_provided':
            return {sub_node: x.get(attribute).get(sub_node)}
        return x.get(attribute)

    fields = ['id', 'name', 'avatar', 'hospital', 'department', 'position', 'title', 'speciality', 'description', 'service_provided']
    x = doctor.to_bson(fields=fields)
    x.update({'qrcode_image': qrcode_image(doctor)})
    x.update({'qrcode_url': qrcode_url(doctor)})
    x.update({'homepage': homepage(doctor)})

    x.update({'assistant_name': '客服'})
    x.update({'assistant_mobile': '4000-6868-95'})
    if doctor.assistant:
        assistant = DoctorAssistant.objects(id=doctor.assistant).first()
        if assistant:
            x.update({'assistant_name': assistant.name})
            x.update({'assistant_mobile': assistant.mobile})

    x.update({'security_code': security_code_status(doctor)})
    return x


def handle_service_provided(service_provided):
    if service_provided is None:
        return None
    temp = {}
    for service in [k for k, v in app.config.get('SERVICE_TYPE')]:
        if service in service_provided:
            value = service_provided.get(service)
            if not isinstance(value, dict):
                continue
            if service == 'phonecall':
                minutes_min = 10000
                for x in ('price_05', 'price_10', 'price_15', 'price_20'):
                    if value.get(x) != -1:
                        minutes_min = int(x.replace('price_', ''))
                        break
                value.update({'minutes_min': minutes_min})
            price = value.get('price')
            if isinstance(price, str):
                if price.isdigit():
                    value.update({'price': int(price)})
                else:
                    value.update({'price': 0})
            temp.update({service: Doctor._service_provided(**value)})
    return temp


@rpc_doctor.route('/private', methods=['GET', 'POST'])
@auth_required('doctor')
def private():
    if request.method == 'POST':
        doctor = DoctorPrivate.objects(id=g.role_instance.id).first()
        if not doctor:
            doctor = DoctorPrivate()
            doctor.id = g.role_instance.id
            doctor.uref = g.role_instance
        g.message = '设置成功'
        for k, v in g.form.items():
            # if k in ('revenue'):
            #     continue
            if k == 'bank_card':
                v, bank_card_bind = handle_bank_card(v)
                setattr(doctor, 'bank_card_bind', bank_card_bind)
            setattr(doctor, k, v)
        doctor.updated_at = datetime.utcnow()
        doctor.save()
    return display({'doctor_private': private_info(g.role_instance)})


def private_info(doctor):
    doctor = DoctorPrivate.objects(id=doctor.id).first()
    if doctor:
        return doctor.to_bson()
    return {}


def handle_bank_card(bank_card):
    data = {}
    if bank_card is None:
        return data, False
    bank_card_count = 0
    for k in ('bank', 'city', 'branch', 'card'):
        v = str(bank_card.get(k, ''))
        v = '' if v == 'None' else v
        data.update({k: v})
        bank_card_count += 0 if v == '' else 1
    if bank_card_count == 0:
        g.message = '银行卡解绑成功'
    if bank_card_count == 4:
        g.message = '银行卡绑定成功'
    return data, bank_card_count == 4


@rpc_doctor.route('/revenue', methods=['POST'])
@auth_required('doctor')
def revenue():
    month_str = g.form.get('month', '')
    month_str, start_time, end_time = helper.month_range(month_str)
    data = {'month': month_str}

    bank_card_bind = False
    doctor_private = DoctorPrivate.objects(id=g.role_instance.id).first()
    if doctor_private:
        bank_card_bind = doctor_private.bank_card_bind

    kwargs = {
        'doctor': g.role_instance,
        'schedule__gte': start_time,
        'schedule__lte': end_time,
        'status__in': ['已支付', '待就诊', '已完成'],
        'service': 'clinic',
    }
    # clinic_revenue = Order.objects(**kwargs).sum('price')
    # clinic_count = Order.objects(**kwargs).count()

    kwargs.update({'service': 'consult'})
    consult_revenue = Order.objects(**kwargs).sum('price')
    consult_count = Order.objects(**kwargs).count()

    kwargs.update({'service': 'phonecall'})
    phonecall_revenue = Order.objects(**kwargs).sum('price')
    phonecall_count = Order.objects(**kwargs).count()

    detail = []
    # detail.append({'service_type': 'clinic', 'service_name': '挂号预约', 'revenue': clinic_revenue, 'count': clinic_count})
    detail.append({'service_type': 'consult', 'service_name': '图文咨询', 'revenue': consult_revenue, 'count': consult_count})
    detail.append({'service_type': 'phonecall', 'service_name': '电话问诊', 'revenue': phonecall_revenue, 'count': phonecall_count})

    data.update({'bank_card_bind': bank_card_bind})
    data.update({'detail': detail})
    # data.update({'revenue': clinic_revenue + consult_revenue + phonecall_revenue})
    # data.update({'count': clinic_count + consult_count + phonecall_count})
    data.update({'revenue': consult_revenue + phonecall_revenue})
    data.update({'count': consult_count + phonecall_count})

    return display({'revenue': data})


@rpc_doctor.route('/timetable/create', methods=['POST'])
@auth_required('doctor')
def timetable_create():
    service = g.form.get('service')
    date = g.form.get('date')
    interval = g.form.get('interval')
    timetable = DoctorTimetable.objects(doctor=g.role_instance, service=service, date=date, interval=interval).first()
    __remain = None
    if not timetable:
        timetable = DoctorTimetable()
        timetable.doctor = g.role_instance
        __remain = timetable.remain
    for k, v in g.form.items():
        if k == 'location':
            v = _extract_location(v)
        if k == 'remain':
            continue
        setattr(timetable, k, v)
    if not timetable.interval:
        g.message = '服务时间错误'
        abort(400)
    if service == 'phonecall':
        phonecall = g.role_instance.service_provided.get('phonecall')
        if phonecall is None:
            g.message = '请先设置价格'
            abort(400)
        timetable.minutes_quantity = _interval_to_quantity(timetable.interval)
        timetable.minutes_remain = timetable.minutes_quantity
    timetable.remain = timetable.quantity
    timetable.updated_at = datetime.utcnow()
    timetable.save()
    timetable.reload()

    g.message = '添加成功'
    return display({'timetables': timetable_list(service)})


def _interval_to_quantity(interval):
    interval = [_interval.split(':') for _interval in interval.split(',')]
    delta = list(map(lambda a, b:int(b) - int(a), *interval))
    return delta[0] * 60 + delta[1]


def _extract_location(location_id):
    g.message = '出诊地址错误'
    location = DoctorLocation.objects(doctor=g.role_instance, id=location_id).first_or_404()
    g.message = None
    value = {
        'id': location.id,
        'hospital': location.hospital,
        'branch': location.branch,
        'address': location.address,
        'info': location.info
    }
    return value


@rpc_doctor.route('/timetable/filter', methods=['POST'])
@auth_required('doctor')
def timetable_filter():
    service = g.form.get('service')
    return display({'timetables': timetable_list(service)})


def _sold_checking(timetable):
    if timetable.quantity > timetable.remain:
        interval = timetable.interval.replace(',', '~')
        sold = '{0}月{1}日{2}'.format(timetable.date[5:7], timetable.date[8:10], interval)
        g.message = '{0} 已有患者预约\n如需调整请联系客服'.format(sold)
        g.status = 901
        abort(409)


@rpc_doctor.route('/timetable/edit', methods=['POST'])
@auth_required('doctor')
def timetable_edit():
    def _edit(timetable):
        for k, v in g.form.items():
            if k not in ('interval', 'price', 'quantity', 'location'):
                continue
            if k == 'location':
                v = _extract_location(v)
            setattr(timetable, k, v)
        if timetable.service == 'phonecall':
            _sold_ = timetable.minutes_quantity - timetable.minutes_remain
            timetable.minutes_quantity = _interval_to_quantity(timetable.interval)
            timetable.minutes_remain = timetable.minutes_quantity - _sold_
        timetable.remain = timetable.quantity
        timetable.updated_at = datetime.utcnow()
        timetable.save()

    id = g.form.pop('id')
    g.message = '日程不存在'
    timetable = DoctorTimetable.objects(doctor=g.role_instance, id=id).first_or_404()
    _sold_checking(timetable)
    _edit(timetable)

    g.message = '修改成功'
    return display({'timetables': timetable_list(timetable.service)})


@rpc_doctor.route('/timetable/delete', methods=['POST'])
@auth_required('doctor')
def timetable_delete():
    id = g.form.pop('id')
    g.message = '日程不存在'
    timetable = DoctorTimetable.objects(doctor=g.role_instance, id=id).first_or_404()
    _sold_checking(timetable)
    timetable.delete()

    g.message = '删除成功'
    return display({'timetables': timetable_list(timetable.service)})


def timetable_list(service):
    starts, ends = Week().limit(3).bounds()
    timetables = DoctorTimetable.objects(doctor=g.role_instance, service=service, date__gte=starts, date__lt=ends).order_by('+date')
    if timetables:
        return [x.to_bson() for x in timetables]
    return []


@rpc_doctor.route('/location/list', methods=['GET'])
@auth_required('doctor')
def location_list():
    locations = DoctorLocation.objects(doctor=g.role_instance)
    if locations:
        locations = [x.to_bson() for x in locations]
    return display({'locations': locations})


@rpc_doctor.route('/location/create', methods=['POST'])
@auth_required('doctor')
def location_create():
    location = DoctorLocation()
    location.doctor = g.role_instance
    for k, v in g.form.items():
        setattr(location, k, v)
    location.updated_at = datetime.utcnow()
    location.save()
    g.message = '添加成功'
    return location_list()


@rpc_doctor.route('/location/edit', methods=['POST'])
@auth_required('doctor')
def location_edit():
    id = g.form.get('id')
    g.message = '地址不存在'
    location = DoctorLocation.objects(doctor=g.role_instance, id=id).first_or_404()
    for k, v in g.form.items():
        setattr(location, k, v)
    location.updated_at = datetime.utcnow()
    location.save()
    g.message = '编辑成功'
    return location_list()


@rpc_doctor.route('/location/delete', methods=['POST'])
@auth_required('doctor')
def location_delete():
    id = g.form.get('id')
    g.message = '地址不存在'
    location = DoctorLocation.objects(doctor=g.role_instance, id=id).first_or_404()
    g.message = '删除成功'
    location.delete()
    return location_list()


def _wrap_dates(date, weekday):
    dates = Week().limit(3).dates().get(int(weekday))
    dates = [x for x in dates if x >= date]
    if date not in dates:
        g.message = '日期错误或超出设置范围'
        abort(400)
    return dates


def security_code_status(doctor):
    doctor_private = DoctorPrivate.objects(id=doctor.id).first()
    if doctor_private and doctor_private.security_code is not None:
        return True
    return False


@rpc_doctor.route('/security_code/isset', methods=['GET'])
@auth_required('doctor')
def security_code_isset():
    doctor = g.role_instance
    return display({'security_code': security_code_status(doctor)})


@rpc_doctor.route('/security_code/set', methods=['POST'])
@auth_required('doctor')
def security_code_set():
    security_code = g.form.get('security_code')
    doctor = DoctorPrivate.objects(id=g.role_instance.id).first()
    if not doctor:
        doctor = DoctorPrivate()
        doctor.id = g.role_instance.id
        doctor.uref = g.role_instance.uref
    # if doctor.security_code is not None:
    #     g.message = '设置失败'
    #     abort(400)
    doctor.security_code = security_code
    doctor.save()
    g.message = '设置成功'
    return display({'doctor': basic_info(g.role_instance)})


@rpc_doctor.route('/security_code/check', methods=['POST'])
@auth_required('doctor')
def security_code_check():
    security_code = g.form.get('security_code')
    doctor = DoctorPrivate.objects(id=g.role_instance.id).first()
    if doctor and doctor.security_code == security_code:
        g.message = '验证成功'
        return display(None)
    return display(None, 404, '验证失败')


def qrcode_image(doctor):
    qrcode = DoctorQRcode.objects(doctor=doctor).first()
    if not qrcode:
        qrcode = DoctorQRcode.objects(doctor__exists=False).first()
        qrcode.doctor = doctor
        qrcode.save()
        doctor.scene_id = qrcode.scene_id
        doctor.save()
    qr_image_url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket={0}'
    return qr_image_url.format(qrcode.ticket)


def qrcode_url(doctor):
    if g.run_mode == 'development':
        url = 'http://h5test.huimeibest.com/doctor/qrcode/{0}'
    else:
        url = 'http://h5.huimeibest.com/doctor/qrcode/{0}'
    return url.format(doctor.id)


def homepage(doctor):
    if g.run_mode == 'development':
        url = 'http://h5test.huimeibest.com/doctor/details/{0}'
    else:
        url = 'http://h5.huimeibest.com/doctor/details/{0}'
    return url.format(doctor.id)


@rpc_doctor.route('/article/list', methods=['POST'])
@auth_required('doctor')
def article_list():
    since_article_id = g.form.get('since_article_id')
    if since_article_id:
        articles = DoctorArticle.objects(doctor=g.role_instance, id__gt=since_article_id).limit(10)
    else:
        articles = DoctorArticle.objects(doctor=g.role_instance).limit(10)
    if articles:
        articles = [x.to_bson() for x in articles]
    return display({'articles': articles})


# vim:ts=4:sw=4

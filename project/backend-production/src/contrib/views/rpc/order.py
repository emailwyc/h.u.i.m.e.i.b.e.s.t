# vim: set fileencoding=utf-8 :

from .. import *
from ...models import User, Doctor, Patient, Order, OrderComment
from ...libs.cloopen import CloOpen

rpc_order = Blueprint('order', __name__, url_prefix='/rpc/order')


@rpc_order.route('/create', methods=['POST'])
@auth_required('patient')
def create():
    detect_doctor()
    order = Order()
    for k, v in g.form.items():
        setattr(order, k, v)
    order.patient = g.role_instance
    order.status = '新订单'
    order.price = get_price()
    order.updated_at = datetime.utcnow()
    order.save()
    g.message = '订单提交成功'
    return display({'order': order_show(patient=g.role_instance, order_id=order.id)})


def detect_doctor():
    doctor_id = g.form.get('doctor')
    user = User.objects(id=doctor_id).first_or_404()
    doctor = Doctor.objects(id=user.id).first_or_404()
    g.form.update({'doctor': doctor})
    g.doctor = doctor


def get_price():
    service = g.form.get('service')
    service_provided = g.doctor.service_provided
    if service in service_provided:
        price = service_provided.get(service).price
    return price


def order_show(patient, order_id=None):
    if order_id:
        order = Order.objects(patient=patient, id=order_id).first()
        if order:
            return order.to_bson('doctor', 'patient')
        return None
    else:
        order = Order.objects(patient=patient)
        if order:
            return [x.to_bson() for x in order]
        return []


@rpc_order.route('/daily', methods=['GET'])
@auth_required('doctor')
def daily():
    # orders = Order.objects(doctor=g.role_instance).distinct('schedule')
    # return display({'order_daily': orders})
    abort(410)


@rpc_order.route('/history', methods=['POST'])
@auth_required('doctor')
def history():
    start_time = g.form.get('start_time')
    end_time = g.form.get('end_time')
    service = g.form.get('service')
    sort = g.form.get('sort')
    _service = ['clinic', 'consult', 'phonecall'] if not service else [service]
    _sort = '-schedule' if sort == 'desc' else '+schedule'
    _status = ['已支付', '待就诊', '已就诊', '已完成', '已取消']
    orders = Order.objects(doctor=g.role_instance, schedule__gte=start_time, schedule__lt=end_time,
                           service__in=_service, status__in=_status).order_by(_sort)
    status_list = {
        'clinic': ['待就诊', '已就诊', '已取消'],
        'consult': [],
        'phonecall': ['待就诊', '已就诊', '已取消'],
    }
    _status_list = dict([(key, status_list.get(key, [])) for key in _service])

    if orders:
        orders = [_order_wrapper_for_history(x) for x in orders]
    return display({'orders_history': orders, 'status_list': _status_list})


@rpc_order.route('/cursor', methods=['POST'])
@auth_required('doctor')
def cursor():
    since_order_id = g.form.get('since_order_id')
    operator = g.form.get('operator')
    operator = operator if operator in ('<', '>') else '<'

    _service = ['clinic', 'consult', 'phonecall']
    _status = ['已支付', '待就诊']

    criteria = {
        'doctor': g.role_instance,
        'service__in': _service,
        'status__in': _status,
    }
    sort = '-created_at'
    if since_order_id:
        if operator == '<':
            criteria.update({'id__lt': since_order_id})
        else:
            criteria.update({'id__gt': since_order_id})
            sort = '+created_at'

    orders = Order.objects(**criteria).order_by(sort).limit(10)
    if orders:
        if sort == '+created_at':
            orders = list(orders)[::-1]
        orders = [_order_wrapper_for_waiting(x) for x in orders]
    return display({'orders_cursor': orders, 'orders_unread': unread()})


def unread():
    _status = ['已支付', '待就诊']
    criteria = {
        'doctor': g.role_instance,
        'status__in': _status,
        'unread': True,
    }
    data = []
    data.append({'service': 'consult',   'count': Order.objects(service='consult', **criteria).count()})
    data.append({'service': 'clinic',    'count': Order.objects(service='clinic', **criteria).count()})
    data.append({'service': 'phonecall', 'count': Order.objects(service='phonecall', **criteria).count()})
    return data


@rpc_order.route('/id', methods=['POST'])
@auth_required('doctor')
def idx():
    id = g.form.pop('id')
    g.message = '订单不存在'
    order = Order.objects(id=id, doctor=g.role_instance).first_or_404()
    return display({'order': _order_wrapper_for_detail(order)})


@rpc_order.route('/read', methods=['POST'])
@auth_required('doctor')
def read():
    id = g.form.pop('id')
    g.message = '订单不存在'
    order = Order.objects(id=id, doctor=g.role_instance).first_or_404()
    g.message = None
    last_unread_status = order.unread
    if last_unread_status is not False:
        order.unread = False
        order.save()
    return display({'last_unread_status': last_unread_status})


@rpc_order.route('/end', methods=['POST'])
@auth_required('doctor')
def end():
    id = g.form.pop('id')
    g.message = '订单错误'

    ''' id is patient_id '''
    # patient = Patient.objects(id=id).first_or_404()
    # order = Order.objects(patient=patient, doctor=g.role_instance).first_or_404()

    ''' id is order_id '''
    order = Order.objects(id=id, doctor=g.role_instance).first_or_404()

    g.message = '本次咨询已结束，感谢您的服务'
    if order.status == '已完成':
        abort(400)
    order.status = '已完成'
    order.updated_at = datetime.utcnow()
    order.save()
    return display(None)


def _order_wrapper_for_detail(order, message=None):
    if order.service == 'phonecall':
        if not order.voip or not order.voip.status:
            order.voip = Order._voip(enable=True, status='开始问诊', call_sid=[], record_url={})
            order.save()

    _order = order.to_bson('patient')
    service = _order.pop('service')

    _order.update({'service_type': service})
    _order.update({'service_name': g.service.get(service)})

    doctor = {
        'name': g.role_instance.name,
        'hospital': g.role_instance.hospital,
        'department': g.role_instance.department,
    }
    _order.update({'doctor': doctor})
    _order.update({'tips': '我们会以 4000686895 呼叫您，帮助您建立与患者的电话沟通，请注意接听。'})

    if service in ('clinic', 'phonecall'):
        if _order.get('status') == '已支付':
            _order.update({'status': '待就诊'})
        if _order.get('status') == '已完成':
            _order.update({'status': '已就诊'})
    if service in ('consult'):
        if _order.get('status') == '已支付':
            _order.update({'status': '咨询中'})
        if _order.get('status') == '已完成':
            _order.update({'status': '已结束'})

    g.message = message
    return _order


def _order_wrapper_for_waiting(order):
    _order = {'patient': order.to_bson('patient').get('patient')}

    for i in ('id', 'name', 'age', 'gender', 'created_at', 'message', 'unread'):
        _order.update({i: getattr(order, i)})

    service = order.service
    _order.update({'service_type': service})
    _order.update({'service_name': g.service.get(service)})

    g.message = ''
    return _order


def _order_wrapper_for_history(order):
    _order = {'patient': order.to_bson('patient').get('patient')}

    for i in ('id', 'name', 'age', 'gender', 'schedule', 'price', 'location', 'created_at', 'message', 'unread', 'status'):
        _order.update({i: getattr(order, i)})

    service = order.service
    _order.update({'service_type': service})
    _order.update({'service_name': g.service.get(service)})

    if service in ('clinic', 'phonecall'):
        if order.status == '已支付':
            _order.update({'status': '待就诊'})
        if order.status == '已完成':
            _order.update({'status': '已就诊'})
    if service in ('consult'):
        if order.status == '已支付':
            _order.update({'status': '咨询中'})
        if order.status == '已完成':
            _order.update({'status': '已结束'})

    g.message = ''
    return _order


@rpc_order.route('/comment', methods=['POST'])
@auth_required('doctor')
def comment():
    def _build_comment(comment):
        patient = Patient.objects(id=comment.patient).first()
        data = {
            'id': comment.id,
            'name': comment.patient_name,
            'gender':  comment.patient_gender,
            'avatar': patient.avatar,
            'content': comment.content,
            'star': comment.star,
            'created_at': comment.created_at,
        }
        return data

    since_comment_id = g.form.get('since_comment_id')
    doctor = str(g.role_instance.id)
    if since_comment_id:
        comments = OrderComment.objects(doctor=doctor, id__lt=since_comment_id).order_by('-id').limit(10)
    else:
        comments = OrderComment.objects(doctor=doctor).order_by('-id').limit(10)

    return display({'comments': [_build_comment(x) for x in comments]})


@rpc_order.route('/voip/calling', methods=['POST'])
@auth_required('doctor')
def voip_calling():
    id = g.form.pop('id')
    g.message = '订单不存在'
    order = Order.objects(id=id, doctor=g.role_instance, service='phonecall').first_or_404()
    g.message = ''
    _status = ['已支付', '待就诊', '已就诊']
    if order.status not in _status:
        g.message = '订单状态错误'
        abort(400)
    response = _call_back_(order.mobile, str(order.id), order.call_minutes*60)
    status_code = response.get('statusCode')
    call_back = response.get('CallBack')
    if status_code == '000000':
        order.voip.enable = False
        order.voip.call_sid.append(call_back.get('callSid'))
        order.voip.status = '连线中'
        order.save()
        g.message = '呼叫中 ...'
    else:
        g.message = '呼叫失败，请重试'
    return display({'order': _order_wrapper_for_detail(order, message=g.message)})


def _call_back_(callee, user_data, max_call_time, caller_showing='4000686895', callee_showing='4000686895'):
    caller = g.role_instance.uref.mobile
    if caller == callee:
        g.message = '主叫被叫相同'
        abort(400)

    app_id = '8a48b55151eb7d520151ec93ab110353'
    if g.run_mode == 'development':
        sub_account_sid = 'fcbd00aeadfb11e59288ac853d9f54f2'
        sub_account_token = '17369f19ec0ffff3a1d971f4fffa8e5f'
    else:
        sub_account_sid = '3494e6feba7911e59288ac853d9f54f2'
        sub_account_token = 'fcb08019b41f9b8df2a22533b9529013'

    debug = True if g.run_mode == 'development' else False

    cloopen = CloOpen(debug=debug)
    cloopen.init_app_id(app_id)
    cloopen.init_sub_account(sub_account_sid, sub_account_token)

    return cloopen.action_call_back(caller, callee, caller_showing, callee_showing, user_data, max_call_time)


# vim:ts=4:sw=4

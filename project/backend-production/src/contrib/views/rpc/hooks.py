# vim: set fileencoding=utf-8 :

from .. import *
from ...models import User, Doctor, Patient, Tag, Order, Setting, VoIPLog
from ...libs.easemob import Easemob
from ...libs.message_push import MessagePush
from bson import ObjectId
from datetime import timedelta
from datetime import date as dt
import xml.etree.ElementTree as ET
from ...libs.oss.oss import OSS
import requests
import os
import math
import time
from ...libs.submail.app_configs import MESSAGE_CONFIGS
from ...libs.submail.message_xsend import MESSAGEXsend
import calendar


rpc_hooks = Blueprint('hooks', __name__, url_prefix='/hooks/order')


'''
订单创建成功之后，调用这个接口

开发环境：http://api-staging.huimeibest.com/hooks/order/$order_id/$md5
生产环境：http://api.huimeibest.com/hooks/order/$order_id/$md5

请求方法：GET

参数：
$order_id 新订单的 id
$md5 = md5($order_id . 'huimei123456')
'''


@rpc_hooks.route('/<order_id>/<hash_txt>', methods=['GET'])
def order(order_id, hash_txt):
    id = _verify_order_id(order_id, hash_txt)
    order = Order.objects(id=id).first_or_404()
    order.unread = True
    order.save()

    default_tag = Tag.objects(doctor=order.doctor, scope='default').first()
    if not default_tag:
        _init_default_tag(order.doctor)
        print('hooks.order: init default tag for doctor: {0}'.format(order.doctor.id))
    elif not Tag.objects(doctor=order.doctor, patients=order.patient):
        Tag.objects(id=default_tag.id).update_one(add_to_set__patients=order.patient)
        print('hooks.order: doctor {2} add patient {0} to tag {1}'.format(order.patient.id, default_tag.id, order.doctor.id))
    else:
        print('hooks.order: doctor {1} has patient {0} existed'.format(order.patient.id, order.doctor.id))

    try:
        _push(order)
    except:
        pass

    return 'ok'


def _verify_order_id(order_id, hash_txt):
    hashed = helper.common_hashed(order_id, 'huimei123456')
    try:
        assert hash_txt == hashed
        return ObjectId(order_id)
    except:
        print('hooks order error: {0} {1}'.format(order_id, hash_txt))
        print('hashed: {0}'.format(hashed))
        abort(404)


def _init_default_tag(doctor):
    tag = Tag()
    tag.scope = 'default'
    tag.doctor = doctor
    tag.name = '我的患者'
    orders = Order.objects(doctor=doctor)
    if orders:
        tag.patients = list(set([order.patient for order in orders]))
    tag.save()


def _push(order):
    doctor = order.doctor.id
    patient = order.patient.name
    service = order.service

    schedule = order.schedule
    timestamp = int(calendar.timegm(schedule.timetuple()))
    schedule = datetime.fromtimestamp(timestamp)

    if service == 'clinic':
        ampm = '上午' if schedule.hour < 12 else '下午'
        msg = '患者{patient}预约了{date}日{ampm}的门诊'.format(patient=patient, date=schedule.date(), ampm=ampm)
    elif service == 'phonecall':
        hour = datetime.strftime(schedule, '%H:%M')
        msg = '患者{patient}预约了{date}日{hour}的电话咨询'.format(patient=patient, date=schedule.date(), hour=hour)
    # elif service == 'consult':
    #     msg = '患者{patient}预约了{date}日的图文咨询'.format(patient=patient, date=schedule.date())

    apns_production = False
    audience_template = 'dev_{0}'
    if g.run_mode == 'production':
        apns_production = True
        audience_template = '{0}'
    mp = MessagePush(apns_production=apns_production)
    audience = []
    audience.append(audience_template.format(str(order.doctor.id)))
    mp.audience({'alias': audience})
    mp.message(message=msg, extras={'type':'order', 'service': service, 'order_id': str(order.id)})
    mp.send()
    return


@rpc_hooks.route('/end', methods=['GET'])
def order_end():
    config = app.config.get('EASEMOB')

    def _order_ending(cached, auth):
        instance = Easemob(config=config, auth=auth)

        order = _pickup_order()
        if not order:
            return
        return
        '''
        TODO
        '''
        message = _message_build(order)
        success, result = instance.send_message(**message)
        if success:
            order.status = '已完成'
            order.save()

        auth_current = instance.app_client_auth.get_auth()
        if auth != auth_current:
            cached.token = auth_current.get('token')
            cached.application = auth_current.get('application')
            cached.expiring_at = str(auth_current.get('expiring_at'))
            cached.save()

    cached = Setting.objects(sign='huanxin').first()
    auth = {
        'token': cached.token,
        'application': cached.application,
        'expiring_at': int(cached.expiring_at),
    }

    try:
        return _order_ending(cached, auth)
    except PermissionError:
        try:
            return _order_ending(cached, None)
        except:
            pass


def _pickup_order():
    time_ending = datetime.utcnow() + timedelta(days=-2)
    # return Order.objects(created_at__lt=time_ending, service='consult', status='已支付').order_by('+id').first()
    return Order.objects(created_at__lt=time_ending, service='consult').order_by('+id').first()


def _message_build(order):
    '''
    {
        "msg_type" : "notice_end",
        "msg_content" : "对话超过48小时，已经自动结束。",
        "msg_time" : 1448268480680,
        "order_id" : "5652ba84b7ef6ae2118b4588",
        "nickname" : "陈震",
        "avatar" : "http://hm-img.huimeibest.com/avatar/ae/ae01d9205efab972e8329da5d938a12b.jpg@!256"
    }
    '''
    extras = {
        "msg_type" : "notice_end",
        "msg_content" : "对话超过48小时，已经自动结束。",
        "msg_time" : 1448268480680,
        "order_id" : "5652ba84b7ef6ae2118b4588",
        "nickname" : "陈震",
        "avatar" : "http://hm-img.huimeibest.com/avatar/ae/ae01d9205efab972e8329da5d938a12b.jpg@!256"
    }
    msg = {}
    msg.update({'msg_from': ''})
    msg.update({'msg_to': ''})
    msg.update({'message': '[已结束]'})
    msg.update({'extras': extras})
    return msg


@rpc_hooks.route('/ccp-privilege/CallAuth', methods=['POST'])
def call_auth():
    request_dict = _xml_to_dict(request.get_data())
    order_id = request_dict.get('userData')
    call_sid = request_dict.get('callSid')
    order = Order.objects(service='phonecall', id=order_id, voip__call_sid=call_sid).first_or_404()
    order.voip.status = '连线中'
    order.save()
    response_xml = '''<?xml version="1.0" encoding="UTF-8"?>
<Response>
    <statuscode>0000</statuscode>
    <statusmsg>CallAuth Success</statusmsg>
    <record>1</record>
    <recordPoint>0</recordPoint>
    <sessiontime>0</sessiontime>
</Response>
'''
    # print('\n---\n{0}\n'.format(response_xml), flush=True)
    _voip_log(request_dict, response_xml)
    return response_xml


@rpc_hooks.route('/ccp-privilege/CallEstablish', methods=['POST'])
def call_establish():
    request_dict = _xml_to_dict(request.get_data())
    order_id = request_dict.get('userData')
    call_sid = request_dict.get('callSid')
    order = Order.objects(service='phonecall', id=order_id, voip__call_sid=call_sid).first_or_404()
    order.voip.status = '通话中'
    order.save()
    response_xml = '''<?xml version="1.0" encoding="UTF-8"?>
<Response>
    <statuscode>0000</statuscode>
    <statusmsg>CallEstablish Success</statusmsg>
    <billdata>billdata</billdata>
    <sessiontime>0</sessiontime>
</Response>
'''
    # print('\n---\n{0}\n'.format(response_xml), flush=True)
    _voip_log(request_dict, response_xml)
    return response_xml


    '''
    鉴权挂机类型说明

    正常挂机
    1:  通话中取消回拨、直拨和外呼的正常结束通话
    2:  账户欠费或者设置的通话时间到
    3:  回拨通话中主叫挂断，正常结束通话
    4:  回拨通话中被叫挂断，正常结束通话

    通用类型
    -1:  被叫没有振铃就收到了挂断消息
    -2:  呼叫超时没有接通被挂断
    -5:  被叫通道建立了被挂断
    -6:  系统鉴权失败
    -7:  第三方鉴权失败
    -11: 账户余额不足

    直拨类型
    -8:  直拨被叫振铃了挂断

    回拨类型
    -3:  回拨主叫接通了主叫挂断
    -4:  回拨主叫通道创建了被挂断
    -9:  回拨被叫振铃了挂断
    -10: 回拨主叫振铃了挂断
    -14: 回拨取消呼叫(通过取消回拨接口)


    http://docs.yuntongxun.com/index.php/%E9%89%B4%E6%9D%83%E6%8C%82%E6%9C%BA%E7%B1%BB%E5%9E%8B%E8%AF%B4%E6%98%8E
    '''
@rpc_hooks.route('/ccp-privilege/Hangup', methods=['POST'])
def hang_up():
    request_dict = _xml_to_dict(request.get_data())
    order_id = request_dict.get('userData')
    call_sid = request_dict.get('callSid')
    bye_type = request_dict.get('byetype')
    order = Order.objects(service='phonecall', id=order_id, voip__call_sid=call_sid).first_or_404()

    order.status = '待就诊'
    order.voip.status = '开始问诊'
    order.voip.enable = True

    if bye_type in ('1', '2', '3', '4'):
        record_url = request_dict.get('recordurl')
        oss_url = _save_to_oss(record_url, order_id, call_sid)
        order.voip.record_url.update({call_sid: oss_url})
        talk_duration = request_dict.get('talkDuration')
        talk_duration = math.ceil(int(talk_duration)/60)
        order.talk_duration += talk_duration
        if order.talk_duration >= order.call_minutes:
            if order.talk_duration > order.call_minutes:
                order.talk_duration = order.call_minutes
            order.status = '已完成'
            order.voip.status = '通话结束'
            order.voip.enable = False
        else:
            order.status = '已就诊'
            order.voip.status = '继续问诊'
            order.voip.enable = True
    order.save()

    if bye_type in ('-9'):
        _call_failed_send_sms(order.mobile, order.doctor.name, order.name)

    response_xml = '''<?xml version="1.0" encoding="UTF-8"?>
<Response>
    <statuscode>0000</statuscode>
    <statusmsg>Hangup Success</statusmsg>
    <totalfee>0.000000</totalfee>
</Response>
'''
    # print('\n---\n{0}\n'.format(response_xml), flush=True)
    _voip_log(request_dict, response_xml)
    return response_xml


def _xml_to_dict(xml):
    xml = ET.fromstring(xml)
    data = {}
    for child in xml:
        data.update({child.tag: child.text})
    # print('\n***\n{0}\n'.format(data), flush=True)
    return data


def _voip_log(request_dict, response_xml):
    voip_log = VoIPLog()
    voip_log.action = request_dict.get('action')
    voip_log.order_id = request_dict.get('userData')
    voip_log.call_sid = request_dict.get('callSid')
    request_xml = request.get_data()
    voip_log.request_xml = request_xml.decode('utf-8')
    voip_log.request_dict = request_dict
    voip_log.response_xml = response_xml
    voip_log.save()


def _save_to_oss(record_url, order_id, call_sid):

    def _download(record_url):
        time.sleep(5)
        res = requests.get(record_url, stream=True)
        if res.status_code == 404:
            time.sleep(5)
            res = requests.get(record_url, stream=True)
        if res.status_code == 404:
            time.sleep(10)
            res = requests.get(record_url, stream=True)
        if res.status_code != 200:
            return None
        local_filename = '/dev/shm/{0}'.format(record_url.replace('://', '_').replace('/', '-'))
        with open(local_filename, 'wb') as f:
            f.write(res.content)
        return local_filename

    def _upload(local_filename):
        host = 'oss-cn-beijing-internal.aliyuncs.com'
        key_id = 'uLmwkyi2tLw0pj7L'
        key_secret = 'DnNH0hXvDV2zqlf9HaCNrNpLwOBXIb'
        oss = OSS(host, key_id, key_secret).bucket('hm-voip').set_prefix('record/')
        oss_filename = '{0}/{1}-{2}.wav'.format(dt.today().strftime('%Y/%m/%d'), order_id, call_sid)
        _path = oss.upload(oss_filename, local_filename, split=False)
        _prefix = 'http://hm-voip.oss-cn-beijing.aliyuncs.com/'
        oss_url = '{0}{1}'.format(_prefix, _path)
        os.remove(local_filename)
        return oss_url

    local_filename = _download(record_url)
    if local_filename:
        return _upload(local_filename)
    return None



def _call_failed_send_sms(mobile, doctor_name, patient_name):
    submail = MESSAGEXsend(MESSAGE_CONFIGS)
    submail.set_project('hewus1')
    submail.add_to(mobile)
    submail.add_var('patient', patient_name)
    submail.add_var('doctor', doctor_name)
    submail.add_var('service_line', '4000686895')
    x = submail.xsend()


# vim:ts=4:sw=4

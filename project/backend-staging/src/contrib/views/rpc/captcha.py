# vim: set fileencoding=utf-8 :

from .. import *
from ...models import Endpoint, Captcha, Session
from .doctor import basic_info as doctor_basic_info

from ...libs.submail.app_configs import MESSAGE_CONFIGS
from ...libs.submail.message_xsend import MESSAGEXsend

rpc_captcha = Blueprint('captcha', __name__, url_prefix='/rpc/captcha')


@rpc_captcha.route('/xsend', methods=['POST'])
def xsend():
    mobile = g.form.get('mobile')
    captcha = helper.captcha(4)

    item = Captcha.objects(mobile=mobile).first()
    if item:
        if (datetime.utcnow() - item.last_send_at).seconds <= 60:
            g.message = '操作频繁，请稍后再试'
            abort(403)
    else:
        item = Captcha()
        item.mobile = mobile
    item.captcha = captcha
    item.last_send_at = datetime.utcnow()

    submail = MESSAGEXsend(MESSAGE_CONFIGS)
    submail.set_project('DoxMX3')
    submail.add_to(mobile)
    submail.add_var('captcha', captcha)
    x = submail.xsend()

    item.send = x
    item.save()

    if x.get('status') == 'success':
        g.message = '发送成功'
        return display(None)

    g.message = '发送失败'
    abort(400)


@rpc_captcha.route('/verify', methods=['POST'])
def verify():
    mobile = g.form.get('mobile')
    captcha = g.form.get('captcha')

    g.message = '验证码错误'
    item = Captcha.objects(mobile=mobile, captcha=captcha).first_or_404()
    g.message = None

    csrf_token = helper.token()
    item.csrf_token = csrf_token
    item.save()

    data = {'csrf_token': csrf_token}
    return display(data)


def csrf_token_verify():
    csrf_token = g.form.get('csrf_token')
    g.message = '数据校验失败'
    item = Captcha.objects(csrf_token=csrf_token).first_or_404()
    return item.mobile


# vim:ts=4:sw=4

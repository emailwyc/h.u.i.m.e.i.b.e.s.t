# vim: set fileencoding=utf-8 :

from .. import *
from ...models import Endpoint, User, Captcha, Session
from .doctor import basic_info as doctor_basic_info
from .captcha import csrf_token_verify
import hashlib

rpc_user = Blueprint('user', __name__, url_prefix='/rpc/user')


@rpc_user.route('/sign_up', methods=['POST'])
def sign_up():
    mobile = g.form.get('mobile')
    password = g.form.get('password')
    if User.objects(mobile=mobile):
        return display(None, 409, '用户名已存在')
    user = User()
    user.mobile = mobile
    user.salt = helper.salt()
    user.password = helper.password_encrypt(password, user.salt)
    user.save()
    g.message = '注册成功'
    return display(None)


@rpc_user.route('/password/update', methods=['POST'])
@auth_required('doctor')
def password_update():
    password_old = g.form.get('password_old')
    password_new = g.form.get('password_new')
    user = g.role_instance.uref
    if user and helper.password_verify(password_old, user.salt, user.password):
        user.salt = helper.salt()
        user.password = helper.password_encrypt(password_new, user.salt)
        user.save()
        g.message = '密码更新成功'
        return display(None)
    return display(None, 404, '密码更新失败')


@rpc_user.route('/password/reset', methods=['POST'])
def password_reset():
    mobile = csrf_token_verify()
    password = g.form.get('password')

    g.message = '用户不存在'
    user = User.objects(mobile=mobile).first_or_404()

    user.salt = helper.salt()
    user.password = helper.password_encrypt(password, user.salt)
    user.save()

    g.message = '密码重置成功'
    return display(None)


@rpc_user.route('/sign_in', methods=['POST'])
def sign_in():
    mobile = g.form.get('mobile')
    password = g.form.get('password')
    # captcha = g.form.get('captcha')
    user = User.objects(mobile=mobile).first()
    if not user:
        return display(None, 404, '手机号码没有注册')

    '''
    item = Captcha.objects(mobile=mobile).first()

    _privileged_auth_ = False
    if g.run_mode == 'development':
        _privileged_auth_ = ('2015' == captcha)
    else:
        # _privileged_auth_ =  ('2015' == captcha and mobile in ('18513851111', '13718951234'))
        _privileged_auth_ = ('2015' == captcha)

    if (item and item.captcha == captcha) or _privileged_auth_:
    '''
    if user and helper.password_verify(password, user.salt, user.password):
        # password_security_checking(user)
        session_token = session_generator(user)
        last_actived_at = user.actived_at
        user.actived_at = datetime.utcnow()
        user.save()
        data = {
            'last_actived_at': last_actived_at,
            'session_token': session_token,
        }
        if g.role == 'doctor':
            doctor = doctor_basic_info(user)
            if not doctor:
                g.message = '医生未签约，请联系客服。'
                abort(404)
            doctor.pop('description')
            doctor.pop('speciality')
            doctor.pop('service_provided')
            doctor.update({'open_password': helper.open_password(doctor.get('id'))})
            data.update({'doctor': doctor})
        g.message = '登录成功'
        return display(data)
    return display(None, 404, '手机号码或密码错误')
    # return display(None, 404, '手机号码或验证码错误')


@rpc_user.route('/sign_out', methods=['GET'])
@auth_required('doctor', 'patient')
def sign_out():
    Session.objects(session_token=g.session_token).delete()
    g.message = '退出成功'
    return display(None)


@rpc_user.route('/noop', methods=['POST'])
@auth_required('doctor', 'patient')
def noop():
    password_security_checking(g.user)
    if g.form.get('noop') == 'ping':
        return display({'noop': 'pong'})
    abort(404)


def session_generator(user):
    session = Session.objects(user=user).first()
    if not session:
        session = Session()
        session.user = user
    ciphertext = helper.token()
    session.session_token = ciphertext
    session.role = g.role
    session.actived_at = datetime.utcnow()
    session.save()
    return ciphertext


def password_security_checking(user):
    if password_is_default(user):
        g.message = '您的密码过于简单，请修改密码后重新登陆。'
        abort(4011)


def password_is_default(user):
    def md5(plaintext):
        md5sum = hashlib.md5()
        md5sum.update(plaintext.encode('utf-8'))
        return md5sum.hexdigest()

    last_6_of_mobile = md5(user.mobile[5:])
    salt = user.salt
    password = helper.password_encrypt(last_6_of_mobile, salt)
    return user.password == password


@rpc_user.route('/password/reset_to_default', methods=['POST'])
def password_reset_to_default():
    abort(404)
    # token = 'zDCI4drA-7PtH5goY-GfD1BCEc-E05jpprq-P2St'
    # if g.form.get('token') != token:
    #     abort(404)
    # users = User.objects()
    # if users:
    #     import hashlib
    #     def md5(plaintext):
    #         md5sum = hashlib.md5()
    #         md5sum.update(plaintext.encode('utf-8'))
    #         return md5sum.hexdigest()
    #     for user in users:
    #         password = md5(user.mobile[5:])
    #         user.salt = helper.salt()
    #         user.password = helper.password_encrypt(password, user.salt)
    #         user.save()
    # return display({'action': 'password reset to default'})


# vim:ts=4:sw=4

# vim: set fileencoding=utf-8 :

from .. import app, cache, g, request, Blueprint, abort
from .. import display, SERVICE_TYPE
from ..models import Session, Doctor, Patient
from utils import helper
from utils import loader
from datetime import datetime
import time
import sys
import json
import importlib
from functools import wraps
from werkzeug.datastructures import MultiDict, ImmutableMultiDict


SCENES = ('rpc', 'admin', 'storage')


@app.before_request
def _before_request():
    # print('{0} {1}'.format('br ', datetime.now()))
    g.run_mode = app.config.get('RUN_MODE')
    init_scene()
    if not g.scene:
        return None
    if g.scene == 'rpc':
        verify_for_scene_rpc()


@app.after_request
def _after_request(response):
    # print('{0} {1}'.format('ar ', datetime.now()))
    if g.scene == 'rpc':
        response.content_type = 'application/json; charset=utf-8'
        # response.add_etag()
        response.headers.add('X-HM-Sign-Reply', g.get('x_sign'))
    return response


def init_scene():
    g.scene = None
    url_segments = request.path.strip('/').split('/')
    segment = url_segments[0]
    if segment in SCENES:
        if len(url_segments) < 3:
            abort(400)
        g.scene = segment
        g.path = {
            'view': url_segments[1],
            'action': url_segments[2],
        }
        if len(url_segments) == 4:
            g.path.update({'extra': url_segments[3]})


def verify_for_scene_rpc():
    app_keypairs = app.config.get('APP_KEYPAIRS')
    x_id = request.headers.get('X-HM-ID')
    x_sign = request.headers.get('X-HM-Sign')
    g.app_info = app_keypairs.get(x_id, None)
    g.app_version = request.headers.get('X-Hm-App-Version')
    if not g.app_info or not x_sign or ',' not in x_sign:
        g.message = '签名错误'
        abort(403)
    x_sign_hash, x_sign_time = x_sign.split(',', 1)
    # print(helper.request_sign(x_sign_time, g.app_info.get('key')))
    if helper.request_sign(x_sign_time, g.app_info.get('key')) != x_sign_hash.lower():
        g.message = '签名错误'
        abort(403)
    g.x_sign = x_sign
    g.role = g.app_info.get('role')
    init_global_env()
    if request.method == 'POST':
        parse_request_json()


def init_global_env():
    g.service = {}
    [g.service.update({k: v}) for k, v in SERVICE_TYPE]


def parse_request_json():
    g.request_json = request.get_json(silent=True)
    if not g.request_json:
        g.message = '请求数据错误: json'
        abort(400)
    form = ImmutableMultiDict(mapping=g.request_json)
    if g.path.get('extra'):
        action = '{0}_{1}'.format(g.path.get('action'), g.path.get('extra'))
    else:
        action = g.path.get('action')
    try:
        loaded = loader.Loader.form('contrib', '{0}.{1}'.format(g.scene, g.path.get('view')), action)
    except ImportError:
        g.message = 'URL:{0} NOT FOUND'.format(request.path)
        abort(404)
    expect_form_name = loader.underline_to_camel('{0}_form'.format(action))
    if not loaded or expect_form_name != loaded.__name__:
        if g.run_mode == 'development':
            g.message = '系统错误: {0}'.format(expect_form_name)
        else:
            g.message = '接口请求错误'
        abort(400)
    form = loaded(form)
    if form.validate() and form.data:
        g.form = {}
        json_keys = g.request_json.keys()
        for key, value in form.data.items():
            if key not in json_keys:
                continue
            g.form.update({key: value})
    else:
        g.message = request_errors_formatter(form.errors)
        abort(400)


def request_errors_formatter(errors):
    x = []
    for k, v in errors.items():
        if isinstance(v[0], dict):
            x.append(request_errors_formatter(v[0]))
        else:
            x.append("{0}".format('，'.join(v)))
    return '；'.join(x)


def auth_required(*roles):
    def decorator(f):
        @wraps(f)
        def decorated_function(*args, **kwargs):
            x_session_token = request.headers.get('X-HM-Session-Token')
            verify = Session.objects(session_token=x_session_token).first()
            if verify:
                if g.role not in roles:
                    g.message = '没有权限'
                    abort(403)
                g.user = verify.user
                g.session_token = x_session_token
                verify.actived_at = datetime.utcnow()
                verify.save()
                role_instance()
                # helper.insight(g)
            else:
                g.message = '请重新登录'
                abort(401)
            return f(*args, **kwargs)
        return decorated_function
    return decorator


def http_auth_required(f):

    def authenticate():
        _auth = {'WWW-Authenticate': 'Basic realm="Login Required"'}
        return '禁止访问', 401, _auth

    def check_auth(username, password):
        return username == 'qianting' and password == 'qianting123456'

    @wraps(f)
    def decorated(*args, **kwargs):
        auth = request.authorization
        if not auth or not check_auth(auth.username, auth.password):
            return authenticate()
        return f(*args, **kwargs)

    return decorated


def create_if_not_exist():
    def decorator(f):
        @wraps(f)
        def decorated_function(*args, **kwargs):
            g.create_if_not_exist = True
            return f(*args, **kwargs)
        return decorated_function
    return decorator


def cached(timeout=5 * 60):
    def decorator(f):
        @wraps(f)
        def decorated_function(*args, **kwargs):
            cache_key = '{0}-{1}'.format(g.user.id, request.path.replace('/', '-'))
            rv = cache.get(cache_key)
            if rv is not None:
                return rv
            rv = f(*args, **kwargs)
            cache.set(cache_key, rv, timeout=timeout)
            return rv
        return decorated_function
    return decorator


def role_instance():

    def _doctor():
        doctor = Doctor.objects(id=g.user.id).first()
        if doctor is None:
            if g.get('create_if_not_exist', None) is None:
                g.message = '医生不存在'
                abort(404)
            doctor = Doctor()
            doctor.id = g.user.id
            doctor.uref = g.user
            doctor.save()
        g.role_instance = doctor

    def _patient():
        patient = Patient.objects(id=g.user.id).first()
        if patient is None:
            if g.get('create_if_not_exist', None) is None:
                g.message = '患者不存在'
                abort(404)
            patient = Patient()
            patient.id = g.user.id
            patient.uref = g.user
            patient.save()
        g.role_instance = patient

    _hooks = {'doctor': _doctor, 'patient': _patient}
    if g.role not in _hooks:
        g.message = '角色错误'
        abort(404)

    if g.get('role_instance', None) is None:
        _hooks.get(g.role)()


# vim:ts=4:sw=4

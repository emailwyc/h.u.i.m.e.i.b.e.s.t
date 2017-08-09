# vim: set fileencoding=utf-8 :

from .. import *
from ...libs.oss.oss import OSS
import hashlib
from datetime import datetime as dt
import os
from subprocess import Popen, PIPE, STDOUT

rpc_storage = Blueprint('storage', __name__, url_prefix='/storage/object')

_CACHE_DIR_ = '/dev/shm/attachment/'
# _CACHE_DIR_ = '/dev/shm/'

_OBJECT_TYPE_ = {
    'png': 'image',
    'jpg': 'image',
    'jpeg': 'image',
    'amr': 'voice',
    'mp3': 'voice',
}

_EXTENSION_ = tuple(set(_OBJECT_TYPE_.keys()))

app.config['MAX_CONTENT_LENGTH'] = 12 * 1024 * 1024

@rpc_storage.route('/upload', methods=['POST'])
def info():
    attachment = request.files.get('attachment')
    if attachment is None:
        g.message = '请选择文件'
        abort(400)

    extension = _allowed_file_extension(attachment.filename)
    try:
        if hasattr(attachment.stream, 'getvalue'):
            file_content = attachment.stream.getvalue()
        elif hasattr(attachment.stream, 'read'):
            file_content = attachment.stream.read()
        else:
            raise RuntimeError('无法获取上传的文件内容')
    except Exception as e:
        g.message = str(e)
        abort(400)

    file_name = '{0}.{1}'.format(hashlib.sha1(file_content).hexdigest(), extension)
    file_location = '{0}{1}'.format(_CACHE_DIR_, file_name)

    if not os.path.isdir(_CACHE_DIR_):
        os.mkdir(_CACHE_DIR_, mode=0o750)
    with open(file_location, 'wb') as f:
        f.write(file_content)

    data = {
        'url': _upload_to_oss(_OBJECT_TYPE_.get(extension), file_name, file_location),
    }

    if _OBJECT_TYPE_.get(extension) == 'voice' and extension == 'amr':
        file_name_mp3 = '{0}.{1}'.format(file_name.split('.', 1)[0], 'mp3')
        file_location_mp3 = '{0}{1}'.format(_CACHE_DIR_, file_name_mp3)
        if not _amr_to_mp3(file_location, file_location_mp3):
            g.message = 'AMR 转换为 MP3 时出错，请重试。'
            abort(400)
        data.update({'url_mp3': _upload_to_oss(_OBJECT_TYPE_.get(extension), file_name_mp3, file_location_mp3)})
        os.remove(file_location_mp3)

    if _OBJECT_TYPE_.get(extension) == 'image':
        data.update({'url_thumb': '{0}@!max-240'.format(data.get('url'))})

    os.remove(file_location)

    g.message = '上传成功'
    return display({'attachment': data})


def _allowed_file_extension(file_name):
    try:
        extension = file_name.rsplit('.', 1)[1]
        assert extension in _EXTENSION_
        return extension
    except:
        g.message = '文件格式错误'
        abort(400)


def _upload_to_oss(object_type, file_name, file_location):
    if object_type == 'voice':
        _prefix = 'http://hm-attachment.oss-cn-beijing.aliyuncs.com/'
        oss = _build_oss().bucket('hm-attachment').set_prefix('voice/')
    elif object_type == 'image':
        _prefix = 'http://hm-img.huimeibest.com/'
        oss = _build_oss().bucket('hm-img').set_prefix('image/')
    else:
        g.message = '文件存储类型错误'
        abort(400)

    oss_file_name = '{0}/{1}'.format(dt.today().strftime('%Y/%m/%d'), file_name)
    _path = oss.upload(oss_file_name, file_location, split=False)

    return '{0}{1}'.format(_prefix, _path)


def _build_oss():
    host = 'oss-cn-beijing-internal.aliyuncs.com'
    key_id = 'uLmwkyi2tLw0pj7L'
    key_secret = 'DnNH0hXvDV2zqlf9HaCNrNpLwOBXIb'
    return OSS(host, key_id, key_secret)


def _amr_to_mp3(amr, mp3):
    command = 'ffmpeg -loglevel panic -y -i {amr} {mp3}'.format(amr=amr, mp3=mp3)
    child = Popen(command.split(' '), stdin=PIPE, stdout=PIPE, stderr=STDOUT)
    child.wait()
    stdout, stderr = child.communicate()
    if child.returncode == 0:
        if isinstance(stdout, bytes):
            return stdout.decode('utf-8')
        return None
    return False


# vim:ts=4:sw=4

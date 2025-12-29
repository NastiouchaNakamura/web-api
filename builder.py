import os
from shutil import copyfile, rmtree
import sys

print("Buildotron 3000, building from sources!")

input_dir = "src"
output_dir = "out"
print(f"Executed as {' '.join(sys.argv)}, input directory: '{input_dir}', output directory: '{output_dir}' ")

to_parse = [input_dir]
ignored_dirs = [".git", ".gitignore", "readme.md", "readme_template.md", "requests.http", "builder.py", ".out"]
print(f"Starting scan for files to build in '{input_dir}', excluding files and directories: {ignored_dirs}")

to_build = []
while len(to_parse) != 0:
    for f in os.scandir(to_parse.pop()):
        if f.name in ignored_dirs:
            continue
        elif not f.is_file():
            to_parse.append(f.path)
        else:
            to_build.append(f.path)
print(f"Scan done, {len(to_build)} files to build")

if os.path.exists(output_dir):
    rmtree(output_dir)

built_files_count = 0
bytes_count = 0
for src_path in to_build:
    out_path = src_path.replace(input_dir, output_dir, 1)
    os.makedirs(os.path.split(out_path)[0], exist_ok=True)
    if src_path.endswith(".php"):
        copyfile(src_path, out_path)
        built_files_count += 1
        bytes_count += os.stat(out_path).st_size
    else:
        copyfile(src_path, out_path)
        built_files_count += 1
        bytes_count += os.stat(out_path).st_size

print(f"Build done, {built_files_count} files in final build ({bytes_count} bytes), ready to deploy!")
